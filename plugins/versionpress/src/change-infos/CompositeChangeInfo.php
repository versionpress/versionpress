<?php

/**
 * Class representing more changes in one commit
 */
class CompositeChangeInfo implements ChangeInfo {

    /** @var ChangeInfo[] */
    private $changeInfoList;

    /**
     * @param ChangeInfo[] $changeInfoList
     */
    function __construct($changeInfoList) {
        $this->changeInfoList = $changeInfoList;
    }

    /**
     * Creates a commit message from this ChangeInfo. Used by Committer.
     *
     * @see Committer::commit()
     * @return CommitMessage
     */
    function getCommitMessage() {
        $subject = $this->getChangeDescription();

        $bodies = array();
        foreach ($this->changeInfoList as $changeInfo) {
            $bodies[] = $changeInfo->getCommitMessage()->getBody();
        }

        $body = join("\n\n", $bodies);

        return new CommitMessage($subject, $body);
    }

    /**
     * Text displayed in the main VersionPress table (see admin/index.php). Also used
     * to construct commit message subject (first line) when the commit is first
     * physically created.
     *
     * @return string
     */
    function getChangeDescription() {
        $firstChangeDescription = $this->changeInfoList[0]->getChangeDescription();
        $numberOfAnotherChanges = count($this->changeInfoList) - 1; // minus the one which change description is displayed
        return $firstChangeDescription . " and $numberOfAnotherChanges more change" . ($numberOfAnotherChanges > 1 ? "s" : "");
    }

    /**
     * Factory method - builds a ChangeInfo object from a commit message. Used when VersionPress
     * table is constructed; hooks use the normal constructor.
     *
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $fullBody = $commitMessage->getBody();
        $splittedBodies = explode("\n\n", $fullBody);
        $changeInfoList = array();

        foreach($splittedBodies as $body) {
            $partialCommitMessage = new CommitMessage("", $body);
            $changeInfoList[] = ChangeInfoMatcher::createMatchingChangeInfo($partialCommitMessage);
        }

        return new self($changeInfoList);
    }
}