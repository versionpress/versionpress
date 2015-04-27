<?php

namespace VersionPress\ChangeInfos;

use Nette\NotSupportedException;
use VersionPress\Git\CommitMessage;

abstract class BulkChangeInfo implements ChangeInfo {

    /** @var TrackedChangeInfo[] */
    protected $changeInfos;

    /**
     * @param TrackedChangeInfo[] $changeInfos
     */
    public function __construct(array $changeInfos) {
        $this->changeInfos = $changeInfos;
    }

    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        throw new NotSupportedException("Building bulk changeinfo from commit message is not supported");
    }

    public function getCommitMessage() {
        // TODO: Implement getCommitMessage() method.
    }

    /**
     * Returns original ChangeInfo objects.
     *
     * @return TrackedChangeInfo[]
     */
    public function getChangeInfos() {
        return $this->changeInfos;
    }

    function getChangeDescription() {
        return $this->changeInfos[0]->getChangeDescription() . " (and " . (count($this->changeInfos) - 1) . " more)";
    }
}