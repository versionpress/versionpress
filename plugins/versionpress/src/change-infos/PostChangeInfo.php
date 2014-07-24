<?php

class PostChangeInfo extends EntityChangeInfo {

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
        $tags = $commitMessage->getVersionPressTags();
        return parent::matchesCommitMessage($commitMessage) && Strings::startsWith($tags["VP-Action"], "post");
    }

    /**
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    public static function buildFromCommitMessage(CommitMessage $commitMessage)  {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags["VP-Action"];
        list($_, $action, $entityId) = explode("/", $actionTag, 3);
        $titleTag = isset($tags["VP-Post-Title"]) ? $tags["VP-Post-Title"] : $entityId;
        return new self($action, $entityId, $titleTag);
    }

    /**
     * @return string
     */
    public function getChangeDescription() {
        return "Edited post \"{$this->postTitle}\"";
    }

    protected function getCustomTags() {
        return array("VP-Post-Title" => $this->postTitle);
    }
}