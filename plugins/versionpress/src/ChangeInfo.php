<?php

interface ChangeInfo {
    /**
     * @return string
     */
    function getObjectType();

    /**
     * @return string
     */
    function getAction();

    /**
     * @return CommitMessage
     */
    function getCommitMessage();
}

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

class WordPressUpdateChangeInfo implements ChangeInfo {

    /** @var  string */
    private $version;

    public function __construct($version) {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getObjectType() {
        return 'wordpress';
    }

    /**
     * @return string
     */
    public function getAction() {
        return 'update';
    }

    /**
     * @return string
     */
    public function getVersion() {
        return $this->version;
    }

    /**
     * @return CommitMessage
     */
    function getCommitMessage() {
        $messageHead = 'WordPress updated to version ' . $this->version;
        $messageBody = 'VP-Action: wordpress/update/' . $this->version;
        return new CommitMessage($messageHead, $messageBody);
    }
}

class PluginActivationChangeInfo implements ChangeInfo {
    /** @var  string */
    private $pluginName;

    function __construct($pluginName) {
        $this->pluginName = $pluginName;
    }

    /**
     * @return string
     */
    function getObjectType() {
        return 'plugin';
    }

    /**
     * @return string
     */
    function getAction() {
        return 'activate';
    }

    /**
     * @return CommitMessage
     */
    function getCommitMessage() {
        return new CommitMessage("Plugin \"{$this->pluginName}\" was activated", 'VP-Action: plugin/activate/' . $this->pluginName);
    }
}

class PluginDeactivationChangeInfo implements ChangeInfo {
    /** @var  string */
    private $pluginName;

    function __construct($pluginName) {
        $this->pluginName = $pluginName;
    }

    /**
     * @return string
     */
    function getObjectType() {
        return 'plugin';
    }

    /**
     * @return string
     */
    function getAction() {
        return 'deactivate';
    }

    /**
     * @return CommitMessage
     */
    function getCommitMessage() {
        return new CommitMessage("Plugin \"{$this->pluginName}\" was deactivated", 'VP-Action: plugin/deactivate/' . $this->pluginName);
    }
}

class PluginUpdateChangeInfo implements ChangeInfo {
    /** @var  string */
    private $pluginName;

    function __construct($pluginName) {
        $this->pluginName = $pluginName;
    }

    /**
     * @return string
     */
    function getObjectType() {
        return 'plugin';
    }

    /**
     * @return string
     */
    function getAction() {
        return 'update';
    }

    /**
     * @return CommitMessage
     */
    function getCommitMessage() {
        return new CommitMessage("Plugin \"{$this->pluginName}\" was deactivated", 'VP-Action: plugin/update/' . $this->pluginName);
    }
}