<?php

namespace VersionPress\Git;

use Nette\Utils\Strings;
use VersionPress\ChangeInfos\ChangeInfoFactory;
use VersionPress\ChangeInfos\EntityChangeInfo;
use VersionPress\ChangeInfos\UntrackedChangeInfo;
use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Storages\StorageFactory;
use VersionPress\Synchronizers\SynchronizationProcess;
use VersionPress\Utils\ArrayUtils;
use VersionPress\Utils\Comparators;
use VersionPress\Utils\Cursor;
use VersionPress\Utils\IdUtil;
use VersionPress\Utils\ReferenceUtils;

class Reverter
{

    /** @var SynchronizationProcess */
    private $synchronizationProcess;

    /** @var Database */
    private $database;

    /** @var Committer */
    private $committer;

    /** @var GitRepository */
    private $repository;

    /** @var DbSchemaInfo */
    private $dbSchemaInfo;

    /** @var StorageFactory */
    private $storageFactory;

    /** @var ChangeInfoFactory */
    private $changeInfoFactory;

    /** @var int */
    const DELETE_ORPHANED_POSTS_SECONDS = 60;

    public function __construct(
        SynchronizationProcess $synchronizationProcess,
        Database $database,
        Committer $committer,
        GitRepository $repository,
        DbSchemaInfo $dbSchemaInfo,
        StorageFactory $storageFactory,
        ChangeInfoFactory $changeInfoFactory
    ) {
        $this->synchronizationProcess = $synchronizationProcess;
        $this->database = $database;
        $this->committer = $committer;
        $this->repository = $repository;
        $this->dbSchemaInfo = $dbSchemaInfo;
        $this->storageFactory = $storageFactory;
        $this->changeInfoFactory = $changeInfoFactory;
    }

    public function undo($commits)
    {
        return $this->revert($commits, "undo");
    }

    public function rollback($commits)
    {
        return $this->revert($commits, "rollback");
    }

    public function canRevert()
    {
        if (!$this->repository->isCleanWorkingDirectory()) {
            $this->clearOrphanedPosts();
        }
        return $this->repository->isCleanWorkingDirectory();
    }

    private function revert($commits, $method)
    {
        if (!$this->canRevert()) {
            return RevertStatus::NOT_CLEAN_WORKING_DIRECTORY;
        }

        vp_commit_all_frequently_written_entities();
        uasort($commits, function ($a, $b) {
            return $this->repository->wasCreatedAfter($b, $a);
        });

        $modifiedFiles = [];
        $vpIdsInModifiedFiles = [];

        foreach ($commits as $commitHash) {
            $commitHashForDiff = $method === "undo" ? sprintf("%s~1..%s", $commitHash, $commitHash) : $commitHash;
            $modifiedFiles = array_merge($modifiedFiles, $this->repository->getModifiedFiles($commitHashForDiff));
            $modifiedFiles = array_unique($modifiedFiles, SORT_REGULAR);
            $vpIdsInModifiedFiles = array_merge(
                $vpIdsInModifiedFiles,
                $this->getAllVpIdsFromModifiedFiles($modifiedFiles)
            );
            $vpIdsInModifiedFiles = array_unique($vpIdsInModifiedFiles, SORT_REGULAR);

            if ($method === "undo") {
                $status = $this->revertOneCommit($commitHash);
            } else {
                $status = $this->revertToCommit($commitHash);
            }

            if ($status !== RevertStatus::OK) {
                return $status;
            }

            vp_force_action('versionpress', $method, $commitHash, [], [["type" => "path", "path" => "*"]]);
        }

        if (!$this->repository->willCommit()) {
            return RevertStatus::NOTHING_TO_COMMIT;
        }

        $affectedPosts = $this->getAffectedPosts($modifiedFiles);
        $this->updateChangeDateForPosts($affectedPosts);
        $this->committer->commit();

        $vpIdsInModifiedFiles = array_merge($vpIdsInModifiedFiles, $this->getAllVpIdsFromModifiedFiles($modifiedFiles));
        $vpIdsInModifiedFiles = array_unique($vpIdsInModifiedFiles, SORT_REGULAR);

        $this->synchronizationProcess->synchronize($vpIdsInModifiedFiles);

        do_action('vp_revert', $modifiedFiles);
        return RevertStatus::OK;
    }

    private function updateChangeDateForPosts($vpIds)
    {
        $storage = $this->storageFactory->getStorage('post');
        $date = current_time('mysql');
        $dateGmt = current_time('mysql', true);
        foreach ($vpIds as $vpId) {
            $post = $storage->loadEntity($vpId, null);
            if ($post) {
                $sql = "update {$this->database->prefix}posts set post_modified = '{$date}', " .
                    "post_modified_gmt = '{$dateGmt}' where ID = (select id from {$this->database->prefix}vp_id " .
                    "where vp_id = unhex('{$vpId}'))";
                $this->database->query($sql);
                $post['post_modified'] = $date;
                $post['post_modified_gmt'] = $dateGmt;
                $storage->save($post);
            }
        }
    }

    private function getAffectedPosts($modifiedFiles)
    {
        $posts = [];

        foreach ($modifiedFiles as $filename) {
            $match = Strings::match($filename, '~/posts/.*/(.*)\.ini~');
            if ($match) {
                $posts[] = $match[1];
            }
        }

        return $posts;
    }

    private function checkReferencesForRevertedCommit(Commit $revertedCommit)
    {
        $changeInfo = $this->changeInfoFactory->buildChangeInfoEnvelopeFromCommitMessage($revertedCommit->getMessage());

        if ($changeInfo instanceof UntrackedChangeInfo) {
            return true;
        }

        foreach ($changeInfo->getChangeInfoList() as $subChangeInfo) {
            if ($subChangeInfo instanceof EntityChangeInfo &&
                !$this->checkEntityReferences(
                    $subChangeInfo->getScope(),
                    $subChangeInfo->getId(),
                    $subChangeInfo->getParentId()
                )
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns true if there is no reference constraint violation for given entity.
     *
     * @param $entityName
     * @param $entityId
     * @param $parentId
     * @return bool
     */
    private function checkEntityReferences($entityName, $entityId, $parentId)
    {
        $entityInfo = $this->dbSchemaInfo->getEntityInfo($entityName);
        $storage = $this->storageFactory->getStorage($entityName);

        if (!$storage->exists($entityId, $parentId)) {
            return !$this->existsSomeEntityWithReferenceTo($entityName, $entityId);
        }

        $entity = $storage->loadEntity($entityId, $parentId);

        foreach ($entityInfo->references as $reference => $referencedEntityName) {
            $vpReference = "vp_$reference";
            if (!isset($entity[$vpReference]) || $entity[$vpReference] === 0 || $entity[$vpReference] === '0') {
                continue;
            }

            $referencedVpidsString = $entity[$vpReference];
            preg_match_all(IdUtil::getRegexMatchingId(), $referencedVpidsString, $matches);

            foreach ($matches[0] as $referencedVpid) {
                $entityExists = $this->entityExists($referencedEntityName, $referencedVpid, $parentId);
                if (!$entityExists) {
                    return false;
                }
            }
        }

        foreach ($entityInfo->mnReferences as $reference => $referencedEntityName) {
            $vpReference = "vp_$referencedEntityName";
            if (!isset($entity[$vpReference])) {
                continue;
            }

            foreach ($entity[$vpReference] as $referencedEntityId) {
                $entityExists = $this->entityExists($referencedEntityName, $referencedEntityId, $parentId);
                if (!$entityExists) {
                    return false;
                }
            }
        }

        foreach ($entityInfo->valueReferences as $reference => $referencedEntityName) {
            list($sourceColumn, $sourceValue, $valueColumn, $pathInStructure) =
                array_values(ReferenceUtils::getValueReferenceDetails($reference));

            if (!isset($entity[$sourceColumn]) || ($entity[$sourceColumn] !== $sourceValue
                    && !ReferenceUtils::valueMatchesWildcard($sourceValue, $entity[$sourceColumn]))
                || !isset($entity[$valueColumn])
            ) {
                continue;
            }

            if ((is_numeric($entity[$valueColumn]) && intval($entity[$valueColumn]) === 0)
                || $entity[$valueColumn] === '') {
                continue;
            }

            if ($referencedEntityName[0] === '@') {
                $entityNameProvider = substr($referencedEntityName, 1); // strip the '@'
                $referencedEntityName = call_user_func($entityNameProvider, $entity);
                if (!$referencedEntityName) {
                    continue;
                }
            }

            if ($pathInStructure) {
                $entity[$valueColumn] = unserialize($entity[$valueColumn]);
                $paths = ReferenceUtils::getMatchingPaths($entity[$valueColumn], $pathInStructure);
            } else {
                $paths = [[]]; // root = the value itself
            }

            /** @var Cursor[] $cursors */
            $cursors = array_map(function ($path) use (&$entity, $valueColumn) {
                return new Cursor($entity[$valueColumn], $path);
            }, $paths);

            foreach ($cursors as $cursor) {
                $vpidsString = $cursor->getValue();

                preg_match_all(IdUtil::getRegexMatchingId(), $vpidsString, $matches);

                foreach ($matches[0] as $referencedVpid) {
                    $entityExists = $this->entityExists($referencedEntityName, $referencedVpid, $parentId);
                    if (!$entityExists) {
                        return false;
                    }
                }
            }

            if ($pathInStructure) {
                $entity[$valueColumn] = serialize($entity[$valueColumn]);
            }
        }

        return true;
    }

    /**
     * Returns true if there is any entity with reference to the passed one.
     *
     * @param $entityName
     * @param $entityId
     * @return bool
     */
    private function existsSomeEntityWithReferenceTo($entityName, $entityId)
    {
        $entityNames = $this->dbSchemaInfo->getAllEntityNames();

        foreach ($entityNames as $otherEntityName) {
            $otherEntityInfo = $this->dbSchemaInfo->getEntityInfo($otherEntityName);
            $otherEntityReferences = $otherEntityInfo->references;
            $otherEntityMnReferences = $otherEntityInfo->mnReferences;
            $otherEntityValueReferences = $otherEntityInfo->valueReferences;

            $allReferences = array_merge($otherEntityReferences, $otherEntityMnReferences, $otherEntityValueReferences);


            foreach ($allReferences as $reference => $referencedEntity) {
                // if the target is dynamic, check it anyway - just to be sure
                if ($referencedEntity !== $entityName && $referencedEntity[0] !== '@') {
                    continue;
                }

                $otherEntityStorage = $this->storageFactory->getStorage($otherEntityName);
                $possiblyReferencingEntities = $otherEntityStorage->loadAll();

                if (isset($otherEntityReferences[$reference])) { // 1:N reference
                    $vpReference = "vp_$reference";

                    foreach ($possiblyReferencingEntities as $possiblyReferencingEntity) {
                        if (isset($possiblyReferencingEntity[$vpReference])) {
                            $referencedVpidsString = $possiblyReferencingEntity[$vpReference];
                            preg_match_all(IdUtil::getRegexMatchingId(), $referencedVpidsString, $matches);

                            if (ArrayUtils::any($matches[0], Comparators::equals($entityId))) {
                                return true;
                            }
                        }
                    }
                } elseif (isset($otherEntityMnReferences[$reference])) { // M:N reference
                    $vpReference = "vp_$otherEntityName";
                    foreach ($possiblyReferencingEntities as $possiblyReferencingEntity) {
                        if (isset($possiblyReferencingEntity[$vpReference])
                            && array_search($entityId, $possiblyReferencingEntity[$vpReference]) !== false) {
                            return true;
                        }
                    }
                } elseif (isset($otherEntityValueReferences[$reference])) { // Value reference
                    list($sourceColumn, $sourceValue, $valueColumn, $pathInStructure) =
                        array_values(ReferenceUtils::getValueReferenceDetails($reference));

                    foreach ($possiblyReferencingEntities as $possiblyReferencingEntity) {
                        if (isset($possiblyReferencingEntity[$sourceColumn])
                            && ($possiblyReferencingEntity[$sourceColumn] === $sourceValue
                                || ReferenceUtils::valueMatchesWildcard(
                                    $sourceValue,
                                    $possiblyReferencingEntity[$sourceColumn]
                                ))
                            && isset($possiblyReferencingEntity[$valueColumn])
                        ) {
                            if ((is_numeric($possiblyReferencingEntity[$valueColumn])
                                    && intval($possiblyReferencingEntity[$valueColumn]) === 0)
                                || $possiblyReferencingEntity[$valueColumn] === '') {
                                continue;
                            }

                            if ($pathInStructure) {
                                $possiblyReferencingEntity[$valueColumn] =
                                    unserialize($possiblyReferencingEntity[$valueColumn]);
                                $paths = ReferenceUtils::getMatchingPaths(
                                    $possiblyReferencingEntity[$valueColumn],
                                    $pathInStructure
                                );
                            } else {
                                $paths = [[]]; // root = the value itself
                            }

                            /** @var Cursor[] $cursors */
                            $cursors = array_map(function ($path) use (&$possiblyReferencingEntity, $valueColumn) {
                                return new Cursor($possiblyReferencingEntity[$valueColumn], $path);
                            }, $paths);

                            foreach ($cursors as $cursor) {
                                $vpidsString = $cursor->getValue();
                                preg_match_all(IdUtil::getRegexMatchingId(), $vpidsString, $matches);

                                if (ArrayUtils::any($matches[0], Comparators::equals($entityId))) {
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    private function getAllVpIdsFromModifiedFiles($modifiedFiles)
    {
        $vpIds = [];
        $vpIdRegex = "/([\\da-f]{32})/i";
        $vpdbName = basename(VP_VPDB_DIR);
        // https://regex101.com/r/yT6mF5/1
        $optionFileRegex = "/.*{$vpdbName}[\\/\\\\]options[\\/\\\\].+[\\/\\\\](.+)\\.ini/i";
        // https://regex101.com/r/zC6dA2/2
        $optionNameRegex = "/^\\[(.*)\\]\\r?$/m";

        foreach ($modifiedFiles as $file) {
            if (!is_file(VP_PROJECT_ROOT . '/' . $file)) {
                continue;
            }

            if (preg_match($optionFileRegex, $file)) {
                $firstLine = fgets(fopen(VP_PROJECT_ROOT . '/' . $file, 'r'));
                preg_match($optionNameRegex, $firstLine, $optionNameMatch);
                $vpIds[] = ['vp_id' => $optionNameMatch[1], 'parent' => null];
            }

            preg_match($vpIdRegex, $file, $matches);
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            $parent = @$matches[0];

            $content = file_get_contents(VP_PROJECT_ROOT . '/' . $file);
            preg_match_all($vpIdRegex, $content, $matches);
            $vpIds = array_merge($vpIds, array_map(function ($vpId) use ($parent) {
                return ['vp_id' => $vpId, 'parent' => $parent];
            }, $matches[0]));
        }
        return $vpIds;
    }

    private function revertOneCommit($commitHash)
    {
        $commit = $this->repository->getCommit($commitHash);
        if ($commit->isMerge()) {
            return RevertStatus::REVERTING_MERGE_COMMIT;
        }
        if (!$this->repository->revert($commitHash)) {
            return RevertStatus::MERGE_CONFLICT;
        }

        $revertedCommit = $this->repository->getCommit($commitHash);
        $referencesOk = $this->checkReferencesForRevertedCommit($revertedCommit);

        if (!$referencesOk) {
            $this->repository->abortRevert();
            return RevertStatus::VIOLATED_REFERENTIAL_INTEGRITY;
        }

        return RevertStatus::OK;
    }

    private function revertToCommit($commitHash)
    {
        $this->repository->revertAll($commitHash);

        if (!$this->repository->willCommit()) {
            return RevertStatus::NOTHING_TO_COMMIT;
        }

        return RevertStatus::OK;
    }

    /**
     * Deletes orphaned files older than 1 minute (due to postponed commits, that has not been used)
     */
    private function clearOrphanedPosts()
    {
        $deleteTimestamp = time() - self::DELETE_ORPHANED_POSTS_SECONDS; // Older than 1 minute
        $orphanedMenuItems = $this->database->get_col(
            $this->database->prepare(
                "SELECT ID FROM {$this->database->posts} AS p
                LEFT JOIN {$this->database->postmeta} AS m ON p.ID = m.post_id
                WHERE post_type = 'nav_menu_item'
                AND post_status = 'draft' AND meta_key = '_menu_item_orphaned' AND meta_value < '%d'",
                $deleteTimestamp
            )
        );

        foreach ((array)$orphanedMenuItems as $menuItemId) {
            wp_delete_post($menuItemId, true);
            $this->committer->discardPostponedCommit('menu-item-' . $menuItemId);
        }
    }

    /**
     * For standard entities just checks the storage.
     * For child entities (like postmeta) loads all entities and checks them.
     *
     * @param $referencedEntityName
     * @param $referencedEntityId
     * @param $maybeParentId
     * @return bool
     */
    private function entityExists($referencedEntityName, $referencedEntityId, $maybeParentId)
    {

        if (!$this->dbSchemaInfo->isChildEntity($referencedEntityName)) {
            return $this->storageFactory->getStorage($referencedEntityName)->exists($referencedEntityId, null);
        }

        // Optimalization for child entities saved within their parents
        if ($this->storageFactory->getStorage($referencedEntityName)->exists($referencedEntityId, $maybeParentId)) {
            return true;
        }

        $allEntities = $this->storageFactory->getStorage($referencedEntityName)->loadAll();
        return isset($allEntities[$referencedEntityId]);
    }
}
