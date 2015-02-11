<?php

namespace VersionPress\Git;

use Committer;
use Nette\Utils\Strings;
use VersionPress\ChangeInfos\ChangeInfoMatcher;
use VersionPress\ChangeInfos\EntityChangeInfo;
use VersionPress\ChangeInfos\RevertChangeInfo;
use VersionPress\ChangeInfos\UntrackedChangeInfo;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Storages\StorageFactory;
use VersionPress\Synchronizers\SynchronizationProcess;
use VersionPress\Utils\ArrayUtils;
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

    public function __construct(SynchronizationProcess $synchronizationProcess, wpdb $database, Committer $committer, GitRepository $repository, DbSchemaInfo $dbSchemaInfo, StorageFactory $storageFactory) {
        $this->synchronizationProcess = $synchronizationProcess;
        $this->database = $database;
        $this->committer = $committer;
        $this->repository = $repository;
        $this->dbSchemaInfo = $dbSchemaInfo;
        $this->storageFactory = $storageFactory;
    }

    public function revert($commitHash) {
        $modifiedFiles = $this->repository->getModifiedFiles(sprintf("%s~1..%s", $commitHash, $commitHash));
        $revertedCommit = $this->repository->getCommit($commitHash);


        if (!$this->repository->revert($commitHash)) {
            return RevertStatus::MERGE_CONFLICT;
        }

        $referencesOk = $this->checkReferencesForRevertedCommit($revertedCommit);

        if (!$referencesOk) {
            $this->repository->abortRevert();
            return RevertStatus::VIOLATED_REFERENTIAL_INTEGRITY;
        }

        $affectedPosts = $this->getAffectedPosts($modifiedFiles);
        $this->updateChangeDateForPosts($affectedPosts);

        $changeInfo = new RevertChangeInfo(RevertChangeInfo::ACTION_UNDO, $commitHash);
        $this->committer->forceChangeInfo($changeInfo);
        $this->committer->commit();

        $entitiesToSynchronize = $this->detectEntitiesToSynchronize($modifiedFiles);

        $this->synchronize($entitiesToSynchronize);
        return RevertStatus::OK;
    }

    public function revertAll($commitHash) {
        $modifiedFiles = $this->repository->getModifiedFiles($commitHash);
        $affectedPosts = $this->getAffectedPosts($modifiedFiles);

        $this->updateChangeDateForPosts($affectedPosts);

        $this->repository->revertAll($commitHash);

        if (!$this->repository->willCommit()) {
            return RevertStatus::NOTHING_TO_COMMIT;
        }

        $changeInfo = new RevertChangeInfo(RevertChangeInfo::ACTION_ROLLBACK, $commitHash);
        $this->committer->forceChangeInfo($changeInfo);
        $this->committer->commit();

        $entitiesToSynchronize = $this->detectEntitiesToSynchronize($modifiedFiles);

        $this->synchronize($entitiesToSynchronize);
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

    private function synchronize($entitiesToSynchronize) {
        $this->synchronizationProcess->synchronize($entitiesToSynchronize);
    }

    private function getAffectedPosts($modifiedFiles) {
        $posts = array();

        foreach ($modifiedFiles as $filename) {
            $match = Strings::match($filename, '~/posts/(.*)\.ini~');
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
                !$this->checkEntityReferences($subChangeInfo->getEntityName(), $subChangeInfo->getEntityId())) {
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
     * @return bool
     */
    private function checkEntityReferences($entityName, $entityId) {
        $entityInfo = $this->dbSchemaInfo->getEntityInfo($entityName);
        $storage = $this->storageFactory->getStorage($entityName);

        if (!$storage->exists($entityId)) {
            return !$this->existsSomeEntityWithReferenceTo($entityName, $entityId);
        }

        $entity = $storage->loadEntity($entityId);

        foreach ($entityInfo->references as $reference => $referencedEntityName) {
            $vpReference = "vp_$reference";
            if (!isset($entity[$vpReference])) {
                continue;
            }

            $entityExists = $this->storageFactory->getStorage($referencedEntityName)->exists($entity[$vpReference]);
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
                $entityExists = $this->storageFactory->getStorage($referencedEntityName)->exists($referencedEntityId);
                if (!$entityExists) {
                    return false;
                }
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

            $allReferences = array_merge($otherEntityReferences, $otherEntityMnReferences);

            $reference = array_search($entityName, $allReferences);

            if ($reference === false) {
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
            } else { // M:N reference
                $vpReference = "vp_$otherEntityName";
                foreach ($possiblyReferencingEntities as $possiblyReferencingEntity) {
                    if (isset($possiblyReferencingEntity[$vpReference]) && array_search($entityId, $possiblyReferencingEntity[$vpReference]) !== false) {
                        return true;
                    }
                }
            }

        }

        return false;
    }

    /**
     * @param string[] $modifiedFiles List of modified files
     * @return string[] List of entity types
     */
    private function detectEntitiesToSynchronize($modifiedFiles) {
        $entitiesToSynchronize = array();

        if ($this->wasModified($modifiedFiles, 'posts')) {
            $entitiesToSynchronize[] = 'post';
            $entitiesToSynchronize[] = 'postmeta';
            $entitiesToSynchronize[] = 'term_relationship';
        }

        if ($this->wasModified($modifiedFiles, 'comments')) {
            $entitiesToSynchronize[] = 'comment';
        }

        if ($this->wasModified($modifiedFiles, 'users.ini')) {
            $entitiesToSynchronize[] = 'user';
            $entitiesToSynchronize[] = 'usermeta';
        }

        if ($this->wasModified($modifiedFiles, 'terms.ini')) {
            $entitiesToSynchronize[] = 'term';
            $entitiesToSynchronize[] = 'term_taxonomy';
        }

        if ($this->wasModified($modifiedFiles, 'options.ini')) {
            $entitiesToSynchronize[] = 'option';
        }

        return $entitiesToSynchronize;
    }

    /**
     * Returns true if any item of array $modifiedFiles contains a substring $pathPart.
     *
     * @param string[] $modifiedFiles
     * @param string $pathPart
     * @return bool
     */
    private function wasModified($modifiedFiles, $pathPart) {
        return ArrayUtils::any($modifiedFiles, function ($file) use ($pathPart) {
            return Strings::contains($file, $pathPart);
        });
    }
}