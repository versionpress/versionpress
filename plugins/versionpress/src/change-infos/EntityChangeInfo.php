<?php

abstract class EntityChangeInfo extends BaseChangeInfo {

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
     * @param CommitMessage $commitMessage
     * @return boolean
     */
    public static function matchesCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        if(!isset($tags[BaseChangeInfo::ACTION_TAG])) return false;

        $actionTag = $tags[BaseChangeInfo::ACTION_TAG];
        return count(explode("/", $actionTag)) === 3; // there are three parts - $entityType, $action and $entityId
    }

    protected function getCommitMessageHead() {
        static $verbs = array(
            'create' => 'Created',
            'edit' => 'Edited',
            'delete' => 'Deleted'
        );

        $shortEntityId = preg_match("/\d/", $this->getEntityId()) ? substr($this->getEntityId(), 0, 4) : $this->getEntityId();
        return sprintf("%s %s '%s'", $verbs[$this->getAction()], $this->getObjectType(), $shortEntityId);
    }

    /**
     * @return string
     */
    protected function getActionTag() {
        return "{$this->getObjectType()}/{$this->getAction()}/{$this->getEntityId()}";
    }
}