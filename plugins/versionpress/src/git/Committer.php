<?php

class Committer
{

    /** @var Mirror */
    private $mirror;
    /** @var  ChangeInfo */
    private $forcedChangeInfo;

    public function __construct(Mirror $mirror)
    {
        $this->mirror = $mirror;
    }

    /**
     * Checks if there is any change. If so, it tries to commit.
     */
    public function commit()
    {
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

    public function forceChangeInfo(ChangeInfo $changeInfo)
    {
        $this->forcedChangeInfo = $changeInfo;
    }

    private function shouldCommit()
    {
        // proof of concept
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