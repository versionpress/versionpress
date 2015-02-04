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

        $this->synchronize();
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

        $this->synchronize();
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

    private function synchronize() {
        $this->synchronizationProcess->synchronize();
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
            if ($subChangeInfo instanceof EntityChangeInfo && !$this->checkEntityReferences($subChangeInfo)) {
                return false;
            }
        }

        return true;
    }

    private function checkEntityReferences(EntityChangeInfo $changeInfo) {
        $entityName = $changeInfo->getEntityName();
        $entityId = $changeInfo->getEntityId();

        $entityInfo = $this->dbSchemaInfo->getEntityInfo($entityName);
        $storage = $this->storageFactory->getStorage($entityName);
        $entity = $storage->loadEntity($entityId);

        foreach ($entityInfo->references as $reference => $referencedEntityName) {
            $vpReference = "vp_$reference";
            if (isset($entity[$vpReference])) {
                $entityExists = $this->storageFactory->getStorage($referencedEntityName)->exists($entity[$vpReference]);
                if (!$entityExists) return false;
            }
        }

        return true;
    }
}