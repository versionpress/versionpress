<?php
namespace VersionPress\Git;

use VersionPress\ChangeInfos\ChangeInfo;
use VersionPress\ChangeInfos\ChangeInfoEnvelope;
use VersionPress\ChangeInfos\TrackedChangeInfo;
use VersionPress\Git\ChangeInfoPreprocessors\ChangeInfoPreprocessor;
use VersionPress\Git\GitConfig;
use VersionPress\Git\GitRepository;
use VersionPress\Storages\Mirror;
use VersionPress\Storages\StorageFactory;
use VersionPress\Utils\FileSystem;
use VersionPress\Utils\Mutex;

/**
 * Creates commits using the `GitStatic` class. By default, it detects the change from the `$mirror` object
 * but it can also be forced by calling the `forceChangeInfo()` method.
 */
class Committer {

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

    public function __construct(Mirror $mirror, GitRepository $repository, StorageFactory $storageFactory) {
        $this->mirror = $mirror;
        $this->repository = $repository;
        $this->storageFactory = $storageFactory;
    }

    /**
     * Checks if there is any change in the `$mirror` and commits it. If there was a forced
     * change set, it takes precedence.
     */
    public function commit() {
        if ($this->commitDisabled) return;

        if (count($this->forcedChangeInfos) > 0) {
            $changeInfoList = $this->forcedChangeInfos;
        } elseif ($this->shouldCommit()) {
            $changeInfoList = array_merge($this->postponedChangeInfos, $this->mirror->getChangeList());
            if (empty($changeInfoList)) {
                return;
            }
        } else {
            return;
        }

        if ($this->commitPostponed) {
            $this->postponeChangeInfo($changeInfoList);
            $this->commitPostponed = false;
            $this->postponeKey = null;
            $this->flushChangeLists();
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

        $changeInfoLists = $this->preprocessChangeInfoList($changeInfoList);

        $mutex = new Mutex(VERSIONPRESS_MIRRORING_DIR,'committer-stage-commit');
        $mutex->lock();

        if (count($this->forcedChangeInfos) === 1) {
            // If there is one forced change info, we can commit all changes made by change info objects emitted from
            // storages. If there will be more forced change info objects in the future, we have to come up with
            // something smarter. For now, it solves WP-430.
            $this->stageRelatedFiles(new ChangeInfoEnvelope($this->mirror->getChangeList()));
        }

        foreach ($changeInfoLists as $listToCommit) {
            $changeInfoEnvelope = new ChangeInfoEnvelope($listToCommit);
            $this->stageRelatedFiles($changeInfoEnvelope);
            $this->repository->commit($changeInfoEnvelope->getCommitMessage(), $authorName, $authorEmail);
        }

        $mutex->release();

        if (count($this->forcedChangeInfos) > 0 && $this->forcedChangeInfos[0] instanceof \VersionPress\ChangeInfos\WordPressUpdateChangeInfo) {
            FileSystem::remove(ABSPATH . 'versionpress.maintenance');
        }

        $this->flushChangeLists();
    }

    /**
     * Removes some ChangeInfo objects and replaces them with another. For example, it replaces post/draft and post/publish
     * actions with single post/create action.
     * @param TrackedChangeInfo[] $changeInfoList
     * @return TrackedChangeInfo[][]
     */
    private function preprocessChangeInfoList($changeInfoList) {
        $preprocessors = array(
            'VersionPress\Git\ChangeInfoPreprocessors\PostChangeInfoPreprocessor',
            'VersionPress\Git\ChangeInfoPreprocessors\PostTermSplittingPreprocessor',
        );

        $changeInfoLists = array($changeInfoList);
        foreach ($preprocessors as $preprocessorClass) {
            /** @var ChangeInfoPreprocessor $preprocessor */
            $preprocessor = new $preprocessorClass();
            $processedLists = array();
            foreach ($changeInfoLists as $changeInfoList) {
                $processedLists = array_merge($processedLists, $preprocessor->process($changeInfoList));
            }
            $changeInfoLists = $processedLists;
        }

        return $changeInfoLists;
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
    public function forceChangeInfo(TrackedChangeInfo $changeInfo) {
        $this->forcedChangeInfos[] = $changeInfo;
    }

    /**
     * All `commit()` calls are ignored after calling this method.
     */
    public function disableCommit() {
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
     * Unsets previously postponed commit.
     *
     * @param string $key Key for postponedChangeInfos commit
     */
    public function discardPostponedCommit($key) {
        $postponed = $this->loadPostponedChangeInfos();
        if (isset($postponed[$key])) {
            unset($postponed[$key]);
            $this->savePostponedChangeInfos($postponed);
        }
    }

    /**
     * Prepends previously postponedChangeInfos ChangeInfo objects to the current one.
     *
     * @param string $key Key for postponedChangeInfos commit
     */
    public function usePostponedChangeInfos($key) {
        $postponed = $this->loadPostponedChangeInfos();
        if (isset($postponed[$key])) {
            $this->postponedChangeInfos = array_merge($this->postponedChangeInfos, $postponed[$key]);
            unset($postponed[$key]);
            $this->savePostponedChangeInfos($postponed);
        }
    }

    /**
     * Returns false in the mid-step of WP update.
     * The update runs an async HTTP request, so there is created a maintenance file that indicates
     * that the update is still running. Without this, there will be two commits for WP update.
     *
     * @return bool
     */
    private function shouldCommit() {
        return !$this->existsMaintenanceFile();
    }

    private function existsMaintenanceFile() {
        $maintenanceFile = ABSPATH . 'versionpress.maintenance';
        return file_exists($maintenanceFile);
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
            } elseif ($change["type"] === "all-storage-files") {
                $entityName = $change["entity"];
                $path = $this->storageFactory->getStorage($entityName)->getPathCommonToAllEntities();
            } elseif ($change["type"] === "path") {
                $path = $change["path"];
            } else {
                continue;
            }

            $this->repository->stageAll($path);
        }
    }

    /**
     * @param ChangeInfo[] $changeInfoList
     */
    private function postponeChangeInfo($changeInfoList) {
        $postponed = $this->loadPostponedChangeInfos();

        if (!isset($postponed[$this->postponeKey])) {
            $postponed[$this->postponeKey] = array();
        }

        $postponed[$this->postponeKey] = $changeInfoList;
        $this->savePostponedChangeInfos($postponed);
    }

    /**
     * @return TrackedChangeInfo[key][]
     */
    private function loadPostponedChangeInfos() {
        $file = VERSIONPRESS_TEMP_DIR . '/' . $this->fileForPostpone;
        if (is_file($file)) {
            $serializedPostponedChangeInfos = file_get_contents($file);
            return unserialize($serializedPostponedChangeInfos);
        }
        return array();
    }

    /**
     * @param TrackedChangeInfo[key][] $postponedChangeInfos
     */
    private function savePostponedChangeInfos($postponedChangeInfos) {
        $file = VERSIONPRESS_TEMP_DIR . '/' . $this->fileForPostpone;
        $serializedPostponedChangeInfos = serialize($postponedChangeInfos);
        file_put_contents($file, $serializedPostponedChangeInfos);
    }

    private function flushChangeLists() {
        $this->mirror->flushChangeList();
        $this->forcedChangeInfos = array();
    }
}
