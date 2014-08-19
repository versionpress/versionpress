<?php

class PluginChangeInfo implements ChangeInfo {

    private static $OBJECT_TYPE = "plugin";
    private static $plugins;

    /** @var  string */
    private $pluginFile;
    /**
     * Values: activate / deactivate / update
     * @var string
     */
    private $action;

    public function __construct($pluginFile, $action) {
        $this->pluginFile = $pluginFile;
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
        return new CommitMessage("Plugin \"{$this->pluginFile}\" was {$this->action}d", ChangeInfo::ACTION_TAG .": {$this->getObjectType()}/{$this->getAction()}/" . $this->pluginFile);
    }

    /**
     * @param CommitMessage $commitMessage
     * @return boolean
     */
    public static function matchesCommitMessage(CommitMessage $commitMessage) {
        return ChangeInfoHelpers::actionTagStartsWith($commitMessage, "plugin");
    }

    /**
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[ChangeInfo::ACTION_TAG];
        list($_, $action, $pluginName) = explode("/", $actionTag, 3);
        return new self($pluginName, $action);
    }

    /**
     * @return string
     */
    public function getChangeDescription() {
        return NStrings::capitalize($this->action) . "d plugin '{$this->getPluginName()}'";
    }

    /**
     * @return string
     */
    private function getPluginName() {
        if(!self::$plugins) {
            self::$plugins = get_plugins();
        }

        return self::$plugins[$this->pluginFile]["Name"];
    }
}