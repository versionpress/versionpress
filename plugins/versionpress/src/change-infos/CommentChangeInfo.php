<?php

class CommentChangeInfo extends EntityChangeInfo {

    const POST_TITLE_TAG = "VP-Comment-PostTitle";
    const AUTHOR_TAG = "VP-Comment-Author";

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
        return parent::matchesCommitMessage($commitMessage) && ChangeInfoHelpers::actionTagStartsWith($commitMessage, "comment");
    }

    /**
     * @return string
     */
    function getChangeDescription() {
        if($this->getAction() === "create")
            return "New comment for post \"{$this->commentedPost}\"";
        if($this->getAction() === "delete")
            return "Deleted comment for post \"{$this->commentedPost}\"";
        return "Edited comment of \"{$this->commentedPost}\" post";
    }

    /**
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[ChangeInfo::ACTION_TAG];
        $commentAuthor = $tags[self::AUTHOR_TAG];
        $commentedPost = $tags[self::POST_TITLE_TAG];
        list($_, $action, $entityId) = explode("/", $actionTag, 3);
        return new self($action, $entityId, $commentAuthor, $commentedPost);
    }

    protected function getCustomTags() {
        return array(
            self::AUTHOR_TAG => $this->commentAuthor,
            self::POST_TITLE_TAG => $this->commentedPost
        );
    }
}