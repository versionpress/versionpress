<?php

/**
 * Used as fallback for commits that were not created by VersionPress
 */
class CustomChangeInfo implements ChangeInfo {

    /**
     * @var CommitMessage
     */
    private $commitMessage;

    public function __construct(CommitMessage $commitMessage) {
        $this->commitMessage = $commitMessage;
    }

    /**
     * @return string
     */
    public function getObjectType() {
        return "";
    }

    /**
     * @return string
     */
    public function getAction() {
        return "";
    }

    /**
     * @return CommitMessage
     */
    public function getCommitMessage() {
        return $this->commitMessage;
    }

    /**
     * @param CommitMessage $commitMessage
     * @return bool
     */
    public static function matchesCommitMessage(CommitMessage $commitMessage) {
        return true;
    }

    /**
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        return new self($commitMessage);
    }

    /**
     * @return string
     */
    function getChangeDescription() {
        return $this->commitMessage->getHead();
    }
}