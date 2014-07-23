<?php

class EntityChangeInfo implements ChangeInfo {

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
        if(!isset($tags["VP-Action"])) return false;

        $actionTag = $tags["VP-Action"];
        return count(explode("/", $actionTag)) === 3; // there are three parts - $entityType, $action and $entityId
    }

    /**
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags["VP-Action"];
        list($entityType, $action, $entityId) = explode("/", $actionTag, 3);
        return new self($entityType, $action, $entityId);
    }

    private function getCommitMessageHead() {
        static $verbs = array(
            'create' => 'Created',
            'edit' => 'Edited',
            'delete' => 'Deleted'
        );

        $formattedEntityId = preg_match("/\d/", $this->getEntityId()) ? substr($this->getEntityId(), 0, 4) : $this->getEntityId();
        return sprintf("%s %s '%s'", $verbs[$this->getAction()], $this->getObjectType(), $formattedEntityId);
    }

    private function getCommitMessageBody() {
        $entityType = $this->getObjectType();
        $action = $this->getAction();
        $id = $this->getEntityId();

        return "VP-Action: $entityType/$action/$id";
    }
}