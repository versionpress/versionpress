<?php

/**
 * Creates commits using the `GitStatic` class. By default, it detects the change from the `$mirror` object
 * but it can also be forced by calling the `forceChangeInfo()` method.
 */
class Committer
{

    /**
     * @var Mirror
     */
    private $mirror;

    /**
     * If there are forced ChangedInfos, they take precedence over changes detected in the `$mirror`.
     *
     * @var TrackedChangeInfo[]
     */
    private $forcedChangeInfos = array();
    /**
     * @var GitRepository
     */
    private $repository;
    /** @var  bool */
    private $commitDisabled;
    /**
     * @var StorageFactory
     */
    private $storageFactory;

    public function __construct(Mirror $mirror, GitRepository $repository, StorageFactory $storageFactory)
    {
        $this->mirror = $mirror;
        $this->repository = $repository;
        $this->storageFactory = $storageFactory;
    }

    /**
     * Checks if there is any change in the `$mirror` and commits it. If there was a forced
     * change set, it takes precedence.
     */
    public function commit()
    {
        if ($this->commitDisabled) return;

        if (count($this->forcedChangeInfos) > 0) {
            FileSystem::remove(get_home_path() . 'versionpress.maintenance'); // todo: this shouldn't be here...
            $changeInfo = count($this->forcedChangeInfos) > 1 ? new CompositeChangeInfo($this->forcedChangeInfos) : $this->forcedChangeInfos[0];
            $this->forcedChangeInfos = array();
        } elseif ($this->shouldCommit()) {
            $changeList = $this->mirror->getChangeList();
            if (empty($changeList)) {
                return;
            }
            $changeInfo = count($changeList) > 1 ? new CompositeChangeInfo($changeList) : $changeList[0];
        } else {
            return;
        }

        if (is_user_logged_in() && is_admin()) {
            $currentUser = wp_get_current_user();
            $authorName = $currentUser->display_name;
            $authorEmail = $currentUser->user_email;
        } else if (defined('WP_CLI') && WP_CLI) {
            $authorName = GitConfig::$wpcliUserName;
            $authorEmail = GitConfig::$wpcliUserEmail;
        } else {
            $authorName = "Non-admin action";
            $authorEmail = "nonadmin@example.com";
        }

        $this->addRelatedFiles($changeInfo);
        $this->repository->commit($changeInfo->getCommitMessage(), $authorName, $authorEmail);
    }

    /**
     * Forces change info to be committed in the next call to `commit()`, overwriting whatever
     * might have been captured by the Mirror.
     *
     * There can be more forced changed infos and they behave the same as more ChangeInfos returned
     * by the Mirror, i.e. are wrapped in a CompositeChangeInfo and sorted by priorities.
     *
     * @param TrackedChangeInfo $changeInfo
     */
    public function forceChangeInfo(TrackedChangeInfo $changeInfo)
    {
        $this->forcedChangeInfos[] = $changeInfo;
    }

    /**
     * All `commit()` calls are ignored after calling this method.
     */
    public function disableCommit()
    {
        $this->commitDisabled = true;
    }

    /**
     * Returns false in the mid-step of WP update.
     * The update runs an async HTTP request, so there is created a maintenance file that indicates
     * that the update is still running. Without this, there will be two commits for WP update.
     *
     * @return bool
     */
    private function shouldCommit()
    {
        if ($this->dbWasUpdated() && $this->existsMaintenanceFile())
            return false;
        return true;
    }

    private function dbWasUpdated()
    {
        $changes = $this->mirror->getChangeList();
        foreach ($changes as $change) {
            if ($change instanceof EntityChangeInfo &&
                $change->getEntityName() == 'option' &&
                $change->getEntityId() == 'db_version'
            )
                return true;
        }
        return false;
    }

    private function existsMaintenanceFile()
    {
        $maintenanceFilePattern = get_home_path() . '*.maintenance';
        return count(glob($maintenanceFilePattern)) > 0;
    }

    /**
     * @param TrackedChangeInfo $changeInfo
     */
    private function addRelatedFiles($changeInfo) {
        if ($changeInfo instanceof CompositeChangeInfo) {
            /** @var TrackedChangeInfo $subChangeInfo */
            foreach ($changeInfo->getChangeInfoList() as $subChangeInfo) {
                $this->addRelatedFiles($subChangeInfo);
            }
            return;
        }

        $changes = $changeInfo->getChangedFiles();

        foreach ($changes as $actionType => $changesForGivenAction) {
            foreach ($changesForGivenAction as $change) {
                if ($change["type"] === "storage-file") {
                    $entityName = $change["entity"];
                    $entityId = $change["id"];
                    $path = $this->storageFactory->getStorage($entityName)->getEntityFilename($entityId);
                } elseif ($change["type"] === "path") {
                    $path = $change["path"];
                } else {
                    continue;
                }

                if ($actionType === "add") {
                    $this->repository->add($path);
                } elseif ($actionType === "delete") {
                    $this->repository->rm($path);
                }
            }
        }
    }
}
