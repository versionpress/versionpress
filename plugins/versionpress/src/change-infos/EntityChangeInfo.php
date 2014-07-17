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