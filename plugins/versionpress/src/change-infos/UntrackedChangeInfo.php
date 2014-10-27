<?php

/**
 * Represents commits that were not created by VersionPress and we don't know
 * much about them. This ChangeInfo type is a fallback type when no better
 * match is found by ChangeInfoMatcher.
 *
 * @see TrackedChangeInfo
 */
class UntrackedChangeInfo implements ChangeInfo {

    /**
     * @var CommitMessage
     */
    private $commitMessage;

    public function __construct(CommitMessage $commitMessage) {
        $this->commitMessage = $commitMessage;
    }

    /**
     * @inheritdoc
     *
     * @return CommitMessage
     */
    public function getCommitMessage() {
        return $this->commitMessage;
    }

    /**
     * @inheritdoc
     *
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        return new self($commitMessage);
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    function getChangeDescription() {
        return $this->commitMessage->getSubject();
    }
}