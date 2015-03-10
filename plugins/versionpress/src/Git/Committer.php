<?php
use VersionPress\ChangeInfos\ChangeInfoEnvelope;
use VersionPress\ChangeInfos\EntityChangeInfo;
use VersionPress\ChangeInfos\TrackedChangeInfo;
use VersionPress\Git\GitConfig;
use VersionPress\Git\GitRepository;
use VersionPress\Storages\Mirror;
use VersionPress\Storages\StorageFactory;
use VersionPress\Utils\FileSystem;

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
    /** @var  bool */
    private $commitPostponed;
    /** @var  string */
    private $postponeKey;
    /**
     * @var StorageFactory
     */
    private $storageFactory;
    private $fileForPostpone = 'postponed-commits';
    private $postponedChangeInfos = array();

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
            $changeInfo = new ChangeInfoEnvelope($this->forcedChangeInfos);
            $this->forcedChangeInfos = array();
        } elseif ($this->shouldCommit()) {
            $changeList =  array_merge($this->postponedChangeInfos, $this->mirror->getChangeList());
            if (empty($changeList)) {
                return;
            }
            $changeInfo = new ChangeInfoEnvelope($changeList);
        } else {
            return;
        }

        if (is_user_logged_in() && is_admin()) {
            $currentUser = wp_get_current_user();
            /** @noinspection PhpUndefinedFieldInspection */
            $authorName = $currentUser->display_name;
            /** @noinspection PhpUndefinedFieldInspection */
            $authorEmail = $currentUser->user_email;
        } else if (defined('WP_CLI') && WP_CLI) {
            $authorName = GitConfig::$wpcliUserName;
            $authorEmail = GitConfig::$wpcliUserEmail;
        } else {
            $authorName = "Non-admin action";
            $authorEmail = "nonadmin@example.com";
        }

        if ($this->commitPostponed) {
            $this->postponeChangeInfo($changeInfo);
            return;
        }

        $this->stageRelatedFiles($changeInfo);
        $this->repository->commit($changeInfo->getCommitMessage(), $authorName, $authorEmail);
    }

    /**
     * Forces change info to be committed in the next call to `commit()`, overwriting whatever
     * might have been captured by the VersionPress\Storages\Mirror.
     *
     * There can be more forced changed infos and they behave the same as more ChangeInfos returned
     * by the VersionPress\Storages\Mirror, i.e. are wrapped in a VersionPress\ChangeInfos\ChangeInfoEnvelope and sorted by priorities.
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
     * The `commit()` method will not affect the repository after calling this method.
     * Instead it will save ChangeInfo objects for commit on the next request.
     *
     * @param string $key Key for postponedChangeInfos commit
     */
    public function postponeCommit($key) {
        $this->commitPostponed = true;
        $this->postponeKey = $key;
    }

    /**
     * Prepends previously postponedChangeInfos ChangeInfo objects to the current one.
     *
     * @param string $key
     */
    public function usePostponedChangeInfos($key) {
        $postponed = $this->loadPostponedChangeInfos();
        $this->postponedChangeInfos = $postponed[$key];
        unset($postponed[$key]);
        $this->savePostponedChangeInfos($postponed);
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
     * Calls Git `add -A` on files that are related to the given $changeInfo.
     * The "exchange format" is an array documented in {@see TrackedChangedInfo::getChangedFiles()}.
     *
     * @param TrackedChangeInfo|ChangeInfoEnvelope $changeInfo
     */
    private function stageRelatedFiles($changeInfo) {
        if ($changeInfo instanceof ChangeInfoEnvelope) {
            /** @var TrackedChangeInfo $subChangeInfo */
            foreach ($changeInfo->getChangeInfoList() as $subChangeInfo) {
                $this->stageRelatedFiles($subChangeInfo);
            }
            return;
        }

        $changes = $changeInfo->getChangedFiles();

        foreach ($changes as $change) {
            if ($change["type"] === "storage-file") {
                $entityName = $change["entity"];
                $entityId = $change["id"];
                $parentId = $change["parent-id"];
                $path = $this->storageFactory->getStorage($entityName)->getEntityFilename($entityId, $parentId);
            } elseif ($change["type"] === "path") {
                $path = $change["path"];
            } else {
                continue;
            }

            $this->repository->stageAll($path);
        }
    }

    /**
     * @param ChangeInfoEnvelope $changeInfoEnvelope
     */
    private function postponeChangeInfo($changeInfoEnvelope) {
        $postponed = $this->loadPostponedChangeInfos();

        if (!isset($postponed[$this->postponeKey])) {
            $postponed[$this->postponeKey] = array();
        }

        $postponed[$this->postponeKey] = array_merge($postponed[$this->postponeKey], $changeInfoEnvelope->getChangeInfoList());
        $this->savePostponedChangeInfos($postponed);
    }

    /**
     * @return TrackedChangeInfo[key][]
     */
    private function loadPostponedChangeInfos() {
        $file = VERSIONPRESS_TEMP_DIR . '/' . $this->fileForPostpone;
        $serializedPostponedChangeInfos = file_get_contents($file);
        return unserialize($serializedPostponedChangeInfos);
    }

    /**
     * @param TrackedChangeInfo[key][] $postponedChangeInfos
     */
    private function savePostponedChangeInfos($postponedChangeInfos) {
        $file = VERSIONPRESS_TEMP_DIR . '/'. $this->fileForPostpone;
        $serializedPostponedChangeInfos = serialize($postponedChangeInfos);
        file_put_contents($file, $serializedPostponedChangeInfos);
    }
}
