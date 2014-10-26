<?php

class PostMetaChangeInfo extends EntityChangeInfo {

    const POST_TITLE_TAG = "VP-Post-Title";
    const POST_TYPE_TAG = "VP-Post-Type";
    const POST_META_KEY = "VP-PostMeta-Key";

    /**
     * @var string
     */
    private $postType;
    /**
     * @var string
     */
    private $postTitle;
    /**
     * @var string
     */
    private $metaKey;

    public function __construct($action, $entityId, $postType, $postTitle, $metaKey) {
        parent::__construct("postmeta", $action, $entityId);
        $this->postType = $postType;
        $this->postTitle = $postTitle;
        $this->metaKey = $metaKey;
    }

    /**
     * @param CommitMessage $commitMessage
     * @return bool
     */
    public static function matchesCommitMessage(CommitMessage $commitMessage) {
        return parent::matchesCommitMessage($commitMessage) && ChangeInfoHelpers::actionTagStartsWith($commitMessage, "postmeta");
    }

    /**
     * @param CommitMessage $commitMessage
     * @return PostChangeInfo
     */
    public static function buildFromCommitMessage(CommitMessage $commitMessage)  {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[BaseChangeInfo::ACTION_TAG];
        list($_, $action, $entityId) = explode("/", $actionTag, 3);
        $titleTag = isset($tags[self::POST_TITLE_TAG]) ? $tags[self::POST_TITLE_TAG] : $entityId;
        $type = $tags[self::POST_TYPE_TAG];
        $metaKey = $tags[self::POST_META_KEY];
        return new self($action, $entityId, $type, $titleTag, $metaKey);
    }

    /**
     * @return string
     */
    public function getChangeDescription() {
        switch($this->getAction()) {
            case "create":
                return "Created option '{$this->metaKey}' for {$this->postType} '{$this->postTitle}'";
            case "delete":
                return "Deleted option '{$this->metaKey}' for {$this->postType} '{$this->postTitle}'";
        }
        return "Edited option '{$this->metaKey}' for {$this->postType} '{$this->postTitle}'";
    }

    protected function getCustomTags() {
        return array(
            self::POST_TITLE_TAG => $this->postTitle,
            self::POST_TYPE_TAG => $this->postType,
            self::POST_META_KEY => $this->metaKey
        );
    }
}