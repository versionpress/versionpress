<?php

/**
 * Creates commits using the `Git` class. By default, it detects the change from the `$mirror` object
 * but it can also be forced by calling the `forceChangeInfo()` method.
 */
class Committer
{

    /**
     * @var Mirror
     */
    private $mirror;

    /**
     * If this is set, takes precedence over changes detected in the `$mirror`.
     *
     * @var  ChangeInfo
     */
    private $forcedChangeInfo;

    /** @var  bool */
    private $commitDisabled;

    public function __construct(Mirror $mirror)
    {
        $this->mirror = $mirror;
    }

    /**
     * Checks if there is any change in the `$mirror` and commits it. If there was a forced
     * change set, it takes precedence.
     */
    public function commit()
    {
        if($this->commitDisabled) return;

        if ($this->forcedChangeInfo) {
            @unlink(get_home_path() . 'versionpress.maintenance'); // todo: this shouldn't be here...
            Git::commit($this->forcedChangeInfo->getCommitMessage());
            $this->forcedChangeInfo = null;
        } elseif ($this->mirror->wasAffected() && $this->shouldCommit()) {
            $changeList = $this->mirror->getChangeList();
            $commitMessage = $changeList[0]->getCommitMessage();

            Git::commit($commitMessage);
        }
    }

    /**
     * Forces change info to be committed in the next call to `commit()`
     *
     * @param ChangeInfo $changeInfo
     */
    public function forceChangeInfo(ChangeInfo $changeInfo)
    {
        $this->forcedChangeInfo = $changeInfo;
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
                $change->getObjectType() == 'option' &&
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
}