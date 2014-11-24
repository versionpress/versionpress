<?php

/**
 * Comment changes.
 *
 * VP tags:
 *
 *     VP-Action: comment/(create|edit|delete|trash|untrash)/VPID
 *     VP-Comment-Author: John Smith
 *     VP-Comment-PostTitle: Hello world
 *
 */
class CommentChangeInfo extends EntityChangeInfo {

    const POST_TITLE_TAG = "VP-Comment-PostTitle";
    const AUTHOR_TAG = "VP-Comment-Author";

    /** @var string */
    private $commentAuthor;

    /** @var string */
    private $commentedPost;

    public function __construct($action, $entityId, $commentAuthor, $commentedPost) {
        parent::__construct("comment", $action, $entityId);
        $this->commentAuthor = $commentAuthor;
        $this->commentedPost = $commentedPost;
    }

    function getChangeDescription() {
        if($this->getAction() === "create")
            return "New comment for post '{$this->commentedPost}'";
        if($this->getAction() === "delete")
            return "Deleted comment for post '{$this->commentedPost}'";
        return "Edited comment for post '{$this->commentedPost}'";
    }

    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[TrackedChangeInfo::ACTION_TAG];
        $commentAuthor = $tags[self::AUTHOR_TAG];
        $commentedPost = $tags[self::POST_TITLE_TAG];
        list(, $action, $entityId) = explode("/", $actionTag, 3);
        return new self($action, $entityId, $commentAuthor, $commentedPost);
    }

    protected function getCustomTags() {
        return array(
            self::AUTHOR_TAG => $this->commentAuthor,
            self::POST_TITLE_TAG => $this->commentedPost
        );
    }
}