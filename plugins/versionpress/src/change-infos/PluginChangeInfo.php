<?php

class PluginChangeInfo implements ChangeInfo {

    private static $OBJECT_TYPE = "plugin";

    /** @var  string */
    private $pluginName;
    /**
     * Values: activate / deactivate / update
     * @var string
     */
    private $action;

    public function __construct($pluginName, $action) {
        $this->pluginName = $pluginName;
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getObjectType() {
        return self::$OBJECT_TYPE;
    }

    /**
     * @return string
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * @return CommitMessage
     */
    public function getCommitMessage() {
        return new CommitMessage("Plugin \"{$this->pluginName}\" was {$this->action}d", "VP-Action: {$this->getObjectType()}/{$this->getAction()}/" . $this->pluginName);
    }

    /**
     * @param CommitMessage $commitMessage
     * @return boolean
     */
    public static function matchesCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        return isset($tags["VP-Action"]) && Strings::startsWith($tags["VP-Action"], self::$OBJECT_TYPE);
    }

    /**
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags["VP-Action"];
        list($_, $action, $pluginName) = explode("/", $actionTag, 3);
        return new self($pluginName, $action);
    }
}