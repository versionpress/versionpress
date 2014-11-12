<?php

/**
 * Class representing more changes in one commit
 */
class CompositeChangeInfo implements ChangeInfo {

    /** @var ChangeInfo[] */
    private $changeInfoList;

    /**
     * List of change info classes ordered by their priorities.
     * They are listed in commits / commit table in this order.
     *
     * @var string[]
     */
    private $priorityOrder = array(
        "WordPressUpdateChangeInfo",
        "VersionPressChangeInfo",
        "PostChangeInfo",
        "CommentChangeInfo",
        "UserChangeInfo",
        "RevertChangeInfo",
        "PluginChangeInfo",
        "ThemeChangeInfo",
        "TermChangeInfo",
        "OptionChangeInfo",
        "PostMetaChangeInfo",
        "UserMetaChangeInfo",
    );

    /**
     * @param ChangeInfo[] $changeInfoList
     */
    public function __construct($changeInfoList) {
        $this->changeInfoList = $changeInfoList;
    }

    /**
     * Creates a commit message from this ChangeInfo. Used by Committer.
     *
     * @see Committer::commit()
     * @return CommitMessage
     */
    public function getCommitMessage() {
        $subject = $this->getChangeDescription();

        $bodies = array();
        foreach ($this->getSortedChangeInfoList() as $changeInfo) {
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
    public function getChangeDescription() {
        $changeList = $this->getSortedChangeInfoList();
        $firstChangeDescription = $changeList[0]->getChangeDescription();
        $numberOfAnotherChanges = count($changeList) - 1; // minus the one which change description is displayed
        return $firstChangeDescription . " and $numberOfAnotherChanges more change" . ($numberOfAnotherChanges > 1 ? "s" : "");
    }

    /**
     * Factory method - builds a ChangeInfo object from a commit message. Used when VersionPress
     * table is constructed; hooks use the normal constructor.
     *
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $fullBody = $commitMessage->getBody();
        $splittedBodies = explode("\n\n", $fullBody);
        $changeInfoList = array();

        foreach($splittedBodies as $body) {
            $partialCommitMessage = new CommitMessage("", $body);
            $changeInfoList[] = ChangeInfoMatcher::createMatchingChangeInfo($partialCommitMessage);
        }

        return new self($changeInfoList);
    }

    /**
     * @return ChangeInfo[]
     */
    private function getSortedChangeInfoList() {
        $changeList = $this->changeInfoList;
        usort($changeList, array($this, 'compareChangeInfoByPriority'));
        return $changeList;
    }

    /**
     * @param ChangeInfo $changeInfo1
     * @param ChangeInfo $changeInfo2
     * @return int
     */
    private function compareChangeInfoByPriority($changeInfo1, $changeInfo2) {
        $class1 = get_class($changeInfo1);
        $class2 = get_class($changeInfo2);

        $priority1 = array_search($class1, $this->priorityOrder);
        $priority2 = array_search($class2, $this->priorityOrder);

        if ($priority1 < $priority2)
            return -1;
        if ($priority1 > $priority2)
            return 1;
        return 0;
    }
}