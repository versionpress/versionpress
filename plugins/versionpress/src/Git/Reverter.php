<?php

namespace VersionPress\Git;

use Nette\Utils\Strings;
use VersionPress\ChangeInfos\ChangeInfoMatcher;
use VersionPress\ChangeInfos\EntityChangeInfo;
use VersionPress\ChangeInfos\RevertChangeInfo;
use VersionPress\ChangeInfos\UntrackedChangeInfo;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Storages\StorageFactory;
use VersionPress\Synchronizers\SynchronizationProcess;
use VersionPress\Utils\ArrayUtils;
use VersionPress\Utils\ReferenceUtils;
use wpdb;

class Reverter {

    /** @var SynchronizationProcess */
    private $synchronizationProcess;

    /** @var wpdb */
    private $database;

    /** @var Committer */
    private $committer;

    /** @var GitRepository */
    private $repository;

    /** @var DbSchemaInfo */
    private $dbSchemaInfo;

    /** @var StorageFactory */
    private $storageFactory;

    /** @var int */
    const DELETE_ORPHANED_POSTS_SECONDS = 60;

    public function __construct(SynchronizationProcess $synchronizationProcess, $wpdb, Committer $committer, GitRepository $repository, DbSchemaInfo $dbSchemaInfo, StorageFactory $storageFactory) {
        $this->synchronizationProcess = $synchronizationProcess;
        $this->database = $wpdb;
        $this->committer = $committer;
        $this->repository = $repository;
        $this->dbSchemaInfo = $dbSchemaInfo;
        $this->storageFactory = $storageFactory;
    }

    public function undo($commitHash) {
        return $this->_revert($commitHash, "undo");
    }

    public function rollback($commitHash) {
        return $this->_revert($commitHash, "rollback");
    }

    public function canRevert() {
        if (!$this->repository->isCleanWorkingDirectory()) {
            $this->clearOrphanedPosts();
        }
        return $this->repository->isCleanWorkingDirectory();
    }

    private function _revert($commitHash, $method) {
        if (!$this->canRevert()) {
            return RevertStatus::NOT_CLEAN_WORKING_DIRECTORY;
        }

        $commitHashForDiff = $method === "undo" ? sprintf("%s~1..%s", $commitHash, $commitHash) : $commitHash;
        $modifiedFiles = $this->repository->getModifiedFiles($commitHashForDiff);
        $vpIdsInModifiedFiles = $this->getAllVpIdsFromModifiedFiles($modifiedFiles);

        if ($method === "undo") {
            $status = $this->revertOneCommit($commitHash);
            $changeInfo = new RevertChangeInfo(RevertChangeInfo::ACTION_UNDO, $commitHash);

        } else {
            $status = $this->revertToCommit($commitHash);
            $changeInfo = new RevertChangeInfo(RevertChangeInfo::ACTION_ROLLBACK, $commitHash);
        }

        if ($status !== RevertStatus::OK) {
            return $status;
        }

        $this->committer->forceChangeInfo($changeInfo);
        $this->committer->commit();

        $vpIdsInModifiedFiles = array_merge($vpIdsInModifiedFiles, $this->getAllVpIdsFromModifiedFiles($modifiedFiles));
        $vpIdsInModifiedFiles = array_unique($vpIdsInModifiedFiles, SORT_REGULAR);

        $this->synchronizationProcess->synchronize($vpIdsInModifiedFiles);
        $affectedPosts = $this->getAffectedPosts($modifiedFiles);
        $this->updateChangeDateForPosts($affectedPosts);

        do_action('vp_revert');
        return RevertStatus::OK;
    }

    private function updateChangeDateForPosts($vpIds) {
        $date = current_time('mysql');
        $dateGmt = current_time('mysql', true);
        foreach ($vpIds as $vpId) {
            $sql = "update {$this->database->prefix}posts set post_modified = '{$date}', post_modified_gmt = '{$dateGmt}' where ID = (select id from {$this->database->prefix}vp_id where vp_id = unhex('{$vpId}'))";
            $this->database->query($sql);
        }
    }

    private function getAffectedPosts($modifiedFiles) {
        $posts = array();

        foreach ($modifiedFiles as $filename) {
            $match = Strings::match($filename, '~/posts/.*/(.*)\.ini~');
            if ($match) {
                $posts[] = $match[1];
            }
        }

        return $posts;
    }

    private function checkReferencesForRevertedCommit(Commit $revertedCommit) {
        $changeInfo = ChangeInfoMatcher::buildChangeInfo($revertedCommit->getMessage());

        if ($changeInfo instanceof UntrackedChangeInfo) {
            return true;
        }

        foreach ($changeInfo->getChangeInfoList() as $subChangeInfo) {
            if ($subChangeInfo instanceof EntityChangeInfo &&
                !$this->checkEntityReferences($subChangeInfo->getEntityName(), $subChangeInfo->getEntityId(), $subChangeInfo->getParentId())) {
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
    private function checkEntityReferences($entityName, $entityId, $parentId) {
        $entityInfo = $this->dbSchemaInfo->getEntityInfo($entityName);
        $storage = $this->storageFactory->getStorage($entityName);

        if (!$storage->exists($entityId, $parentId)) {
            return !$this->existsSomeEntityWithReferenceTo($entityName, $entityId);
        }

        $entity = $storage->loadEntity($entityId, $parentId);

        foreach ($entityInfo->references as $reference => $referencedEntityName) {
            $vpReference = "vp_$reference";
            if (!isset($entity[$vpReference]) || $entity[$vpReference] == 0) {
                continue;
            }

            $referencedEntityId = $entity[$vpReference];
            $entityExists = $this->entityExists($referencedEntityName, $referencedEntityId, $parentId);
            if (!$entityExists) {
                return false;
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
            list($sourceColumn, $sourceValue, $valueColumn) = array_values(ReferenceUtils::getValueReferenceDetails($reference));
            if (!isset($entity[$sourceColumn]) || $entity[$sourceColumn] != $sourceValue || !isset($entity[$valueColumn])) {
                continue;
            }

            $referencedEntityId = $entity[$valueColumn];

            if ($referencedEntityName[0] === '@') {
                $entityNameProvider = substr($referencedEntityName, 1); // strip the '@'
                $referencedEntityName = call_user_func($entityNameProvider, $entity);
                if (!$referencedEntityName) {
                    continue;
                }
            }

            $entityExists = $this->entityExists($referencedEntityName, $referencedEntityId, $parentId);
            if (!$entityExists) {
                return false;
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
    private function existsSomeEntityWithReferenceTo($entityName, $entityId) {
        $entityNames = $this->dbSchemaInfo->getAllEntityNames();

        foreach ($entityNames as $otherEntityName) {
            $otherEntityInfo = $this->dbSchemaInfo->getEntityInfo($otherEntityName);
            $otherEntityReferences = $otherEntityInfo->references;
            $otherEntityMnReferences = $otherEntityInfo->mnReferences;
            $otherEntityValueReferences = $otherEntityInfo->valueReferences;

            $allReferences = array_merge($otherEntityReferences, $otherEntityMnReferences, $otherEntityValueReferences);

            $reference = array_search($entityName, $allReferences);

            if ($reference === false) { // Other entity is not referencing $entityName
                continue;
            }

            $otherEntityStorage = $this->storageFactory->getStorage($otherEntityName);
            $possiblyReferencingEntities = $otherEntityStorage->loadAll();

            if (isset($otherEntityReferences[$reference])) { // 1:N reference
                $vpReference = "vp_$reference";

                foreach ($possiblyReferencingEntities as $possiblyReferencingEntity) {
                    if (isset($possiblyReferencingEntity[$vpReference]) && $possiblyReferencingEntity[$vpReference] === $entityId) {
                        return true;
                    }
                }
            } elseif (isset($otherEntityMnReferences[$reference])) { // M:N reference
                $vpReference = "vp_$otherEntityName";
                foreach ($possiblyReferencingEntities as $possiblyReferencingEntity) {
                    if (isset($possiblyReferencingEntity[$vpReference]) && array_search($entityId, $possiblyReferencingEntity[$vpReference]) !== false) {
                        return true;
                    }
                }
            } elseif (isset($otherEntityValueReferences[$reference])) { // Value reference
                list($sourceColumn, $sourceValue, $valueColumn) = array_values(ReferenceUtils::getValueReferenceDetails($reference));

                foreach ($possiblyReferencingEntities as $possiblyReferencingEntity) {
                    if (isset($possiblyReferencingEntity[$sourceColumn]) && $possiblyReferencingEntity[$sourceColumn] == $sourceValue && isset($possiblyReferencingEntity[$valueColumn]) && $possiblyReferencingEntity[$valueColumn] === $entityId) {
                        return true;
                    }
                }
            }

        }

        return false;
    }

    private function getAllVpIdsFromModifiedFiles($modifiedFiles) {
        $vpIds = array();
        $vpIdRegex = "/([\\da-f]{32})/i";
        $vpdbName = basename(VP_VPDB_DIR);
        // https://regex101.com/r/yT6mF5/1
        $optionFileRegex = "/.*{$vpdbName}[\\/\\\\]options[\\/\\\\].+[\\/\\\\](.+)\\.ini/i";
        // https://regex101.com/r/zC6dA2/2
        $optionNameRegex = "/^\\[(.*)\\]\\r?$/m";

        foreach ($modifiedFiles as $file) {
            if (!is_file(ABSPATH . $file)) {
                continue;
            }

            if (preg_match($optionFileRegex, $file)) {
                $firstLine = fgets(fopen(ABSPATH . $file, 'r'));
                preg_match($optionNameRegex, $firstLine, $optionNameMatch);
                $vpIds[] = array('vp_id' => $optionNameMatch[1], 'parent' => null);
            }

            preg_match($vpIdRegex, $file, $matches);
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            $parent = @$matches[0];

            $content = file_get_contents(ABSPATH . $file);
            preg_match_all($vpIdRegex, $content, $matches);
            $vpIds = array_merge($vpIds, array_map(function ($vpId) use ($parent) { return array('vp_id' => $vpId, 'parent' => $parent); }, $matches[0]));
        }
        return $vpIds;
    }

    private function revertOneCommit($commitHash) {
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

    private function revertToCommit($commitHash) {
        $this->repository->revertAll($commitHash);

        if (!$this->repository->willCommit()) {
            return RevertStatus::NOTHING_TO_COMMIT;
        }

        return RevertStatus::OK;
    }

    /**
     * Deletes orphaned files older than 1 minute (due to postponed commits, that has not been used)
     */
    private function clearOrphanedPosts() {
        $deleteTimestamp = time() - self::DELETE_ORPHANED_POSTS_SECONDS; // Older than 1 minute
        $wpdb = $this->database;
        $orphanedMenuItems = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts AS p LEFT JOIN $wpdb->postmeta AS m ON p.ID = m.post_id WHERE post_type = 'nav_menu_item' AND post_status = 'draft' AND meta_key = '_menu_item_orphaned' AND meta_value < '%d'", $deleteTimestamp ) );

        foreach( (array) $orphanedMenuItems as $menuItemId ) {
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
    private function entityExists($referencedEntityName, $referencedEntityId, $maybeParentId) {

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
