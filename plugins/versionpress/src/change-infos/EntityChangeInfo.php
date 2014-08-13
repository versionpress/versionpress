<?php

abstract class EntityChangeInfo implements ChangeInfo {

    /**
     * Post, comment etc.
     * @var string
     */
    private $entityType;

    /**
     * create, edit, delete etc.
     * @var string
     */
    private $action;

    /**
     * ID in database
     * @var int
     */
    private $entityId;

    /**
     * @param $entityType string
     * @param $action string
     * @param $entityId string
     */
    public function __construct($entityType, $action, $entityId) {
        $this->entityType = $entityType;
        $this->action = $action;
        $this->entityId = $entityId;
    }

    /**
     * @return string
     */
    public function getObjectType() {
        return $this->entityType;
    }

    /**
     * @return string
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * @return int
     */
    public function getEntityId() {
        return $this->entityId;
    }

    /**
     * @return CommitMessage
     */
    public function getCommitMessage() {
        return new CommitMessage($this->getCommitMessageHead(), $this->getCommitMessageBody());
    }

    /**
     * @param CommitMessage $commitMessage
     * @return boolean
     */
    public static function matchesCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        if(!isset($tags[ChangeInfo::ACTION_TAG])) return false;

        $actionTag = $tags[ChangeInfo::ACTION_TAG];
        return count(explode("/", $actionTag)) === 3; // there are three parts - $entityType, $action and $entityId
    }

    protected  function getCommitMessageHead() {
        static $verbs = array(
            'create' => 'Created',
            'edit' => 'Edited',
            'delete' => 'Deleted'
        );

        $shortEntityId = substr($this->getEntityId(), 0, 4);
        return sprintf("%s %s '%s'", $verbs[$this->getAction()], $this->getObjectType(), $shortEntityId);
    }

    private function getCommitMessageBody() {
        $entityType = $this->getObjectType();
        $action = $this->getAction();
        $id = $this->getEntityId();

        $tags = array();
        $tags[ChangeInfo::ACTION_TAG] = "$entityType/$action/$id";

        $customTags = $this->getCustomTags();
        $tags = array_merge($tags, $customTags);

        $body = "";
        foreach ($tags as $tagName => $tagValue) {
            $body .= "$tagName: $tagValue\n";
        }
        return $body;
    }

    protected function getCustomTags() {
        return array();
    }
}