<?php

class CommentChangeInfo extends EntityChangeInfo {

    /**
     * @var string
     */
    private $commentAuthor;
    /**
     * @var string
     */
    private $commentedPost;

    public function __construct($action, $entityId, $commentAuthor, $commentedPost) {
        parent::__construct("comment", $action, $entityId);
        $this->commentAuthor = $commentAuthor;
        $this->commentedPost = $commentedPost;
    }

    public static function matchesCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        return parent::matchesCommitMessage($commitMessage) && Strings::startsWith($tags[ChangeInfo::ACTION_TAG], "comment");
    }

    /**
     * @return string
     */
    function getChangeDescription() {
        if($this->getAction() == "create")
            return "New comment for post \"{$this->commentedPost}\"";
        else
            return "Edited comment of \"{$this->commentedPost}\" post";
    }

    /**
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[ChangeInfo::ACTION_TAG];
        $commentAuthor = $tags["VP-Comment-Author"];
        $commentedPost = $tags["VP-Comment-Post"];
        list($_, $action, $entityId) = explode("/", $actionTag, 3);
        return new self($action, $entityId, $commentAuthor, $commentedPost);
    }

    protected function getCustomTags() {
        return array(
            "VP-Comment-Author" => $this->commentAuthor,
            "VP-Comment-Post" => $this->commentedPost
        );
    }
}