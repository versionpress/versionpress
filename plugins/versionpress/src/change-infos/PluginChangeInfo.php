<?php

class PluginChangeInfo extends BaseChangeInfo {

    private static $OBJECT_TYPE = "plugin";
    const PLUGIN_NAME_TAG = "VP-Plugin-Name";

    /** @var string */
    private $pluginFile;

    /** @var string */
    private $pluginName;
    /**
     * Values: activate / deactivate / update / edit
     * @var string
     */
    private $action;

    public function __construct($pluginFile, $action, $pluginName = null) {
        $this->pluginFile = $pluginFile;
        $this->action = $action;
        $this->pluginName = $pluginName ? $pluginName : $this->findPluginName();
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
        $actionTag = $commitMessage->getVersionPressTag(BaseChangeInfo::ACTION_TAG);
        $pluginName = $commitMessage->getVersionPressTag(self::PLUGIN_NAME_TAG);
        list($_, $action, $pluginFile) = explode("/", $actionTag, 3);
        return new self($pluginFile, $action, $pluginName);
    }

    /**
     * @return string
     */
    public function getChangeDescription() {
        return NStrings::capitalize($this->action) . (NStrings::endsWith($this->action, "e") ? "d" : "ed") . " plugin '{$this->pluginName}'";
    }

    /**
     * @return string
     */
    protected function getActionTag() {
        return "{$this->getObjectType()}/{$this->getAction()}/" . $this->pluginFile;
    }

    /**
     * Returns the first line of commit message
     *
     * @return string
     */
    protected function getCommitMessageHead() {
        return "Plugin \"{$this->pluginFile}\" was {$this->action}" . (NStrings::endsWith($this->action, "e") ? "d" : "ed");
    }

    protected function getCustomTags() {
        return array(
            self::PLUGIN_NAME_TAG => $this->pluginName
        );
    }

    private function findPluginName() {
        $plugins = get_plugins();
        return $plugins[$this->pluginFile]["Name"];
    }
}