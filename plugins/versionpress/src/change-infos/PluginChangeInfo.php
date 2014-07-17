<?php

class PluginChangeInfo implements ChangeInfo {
    /** @var  string */
    private $pluginName;
    /**
     * Values: activate / deactivate / update
     * @var string
     */
    private $action;

    function __construct($pluginName, $action) {
        $this->pluginName = $pluginName;
        $this->action = $action;
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
        return $this->action;
    }

    /**
     * @return CommitMessage
     */
    function getCommitMessage() {
        return new CommitMessage("Plugin \"{$this->pluginName}\" was {$this->action}d", "VP-Action: plugin/{$this->action}/" . $this->pluginName);
    }
}