<?php

/**
 * Class representing more changes in one commit
 */
class CompositeChangeInfo implements ChangeInfo {

    /** @var TrackedChangeInfo[] */
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
     * @param TrackedChangeInfo[] $changeInfoList
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
        return $firstChangeDescription;
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

        foreach ($splittedBodies as $body) {
            $partialCommitMessage = new CommitMessage("", $body);
            $changeInfoList[] = ChangeInfoMatcher::createMatchingChangeInfo($partialCommitMessage);
        }

        return new self($changeInfoList);
    }

    /**
     * Returns all ChangeInfo objects encapsulated in CompositeChangeInfo.
     *
     * @return TrackedChangeInfo[]
     */
    public function getChangeInfoList() {
        return $this->changeInfoList;
    }

    /**
     * @return TrackedChangeInfo[]
     */
    private function getSortedChangeInfoList() {
        $changeList = $this->changeInfoList;
        usort($changeList, array($this, 'compareChangeInfoByPriority'));
        return $changeList;
    }

    /**
     * Compare function for usort()
     *
     * @param TrackedChangeInfo $changeInfo1
     * @param TrackedChangeInfo $changeInfo2
     * @return int If $changeInfo1 is more important, returns -1, and the opposite for $changeInfo2. ChangeInfos
     *   of same priorities return zero.
     */
    private function compareChangeInfoByPriority($changeInfo1, $changeInfo2) {
        $class1 = get_class($changeInfo1);
        $class2 = get_class($changeInfo2);

        $priority1 = array_search($class1, $this->priorityOrder);
        $priority2 = array_search($class2, $this->priorityOrder);

        if ($priority1 < $priority2) {
            return -1;
        }

        if ($priority1 > $priority2) {
            return 1;
        }

        // For two ThemeChangeInfo objects, the "switch" one wins
        // (Note: the type comparisons can be done for one object only as they are of the same type at this point)
        if ($changeInfo1 instanceof ThemeChangeInfo) {

            if ($changeInfo1->getAction() == "switch") {
                return -1;
            } else if ($changeInfo2->getAction() == "switch") {
                return 1;
            } else {
                return 0;
            }

        }


        if ($changeInfo1 instanceof EntityChangeInfo) {

            // Generally, the "create" action takes precedence
            if ($changeInfo1->getAction() === "create") {
                return 1;
            }

            if ($changeInfo2->getAction() === "create") {
                return -1;
            }

            return 0;

        }

        return 0;
    }
}
