<?php

class PostChangeInfo extends EntityChangeInfo {

    const POST_TITLE_TAG = "VP-Post-Title";

    /**
     * @var string
     */
    private $postTitle;

    public function __construct($action, $entityId, $postTitle) {
        parent::__construct("post", $action, $entityId);
        $this->postTitle = $postTitle;
    }

    /**
     * @param CommitMessage $commitMessage
     * @return bool
     */
    public static function matchesCommitMessage(CommitMessage $commitMessage) {
        return parent::matchesCommitMessage($commitMessage) && ChangeInfoHelpers::actionTagStartsWith($commitMessage, "post");
    }

    /**
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    public static function buildFromCommitMessage(CommitMessage $commitMessage)  {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[ChangeInfo::ACTION_TAG];
        list($_, $action, $entityId) = explode("/", $actionTag, 3);
        $titleTag = isset($tags[self::POST_TITLE_TAG]) ? $tags[self::POST_TITLE_TAG] : $entityId;
        return new self($action, $entityId, $titleTag);
    }

    /**
     * @return string
     */
    public function getChangeDescription() {
        switch($this->getAction()) {
            case "create":
                return "Created post \"{$this->postTitle}\"";
            case "trash":
                return "Post \"{$this->postTitle}\" moved to trash";
            case "untrash":
                return "Post \"{$this->postTitle}\" moved from trash";
            case "delete":
                return "Deleted post \"{$this->postTitle}\"";
        }
        return "Edited post \"{$this->postTitle}\"";
    }

    protected function getCustomTags() {
        return array(self::POST_TITLE_TAG => $this->postTitle);
    }

    protected function getCommitMessageHead() {
        if ($this->getAction() === 'trash' || $this->getAction() === 'untrash') {
            $preposition = $this->getAction() === 'trash' ? 'to' : 'from';
            $shortEntityId = substr($this->getEntityId(), 0, 4);
            return sprintf("Post '%s' moved %s the trash", $shortEntityId, $preposition);
        }

        return parent::getCommitMessageHead();
    }


}