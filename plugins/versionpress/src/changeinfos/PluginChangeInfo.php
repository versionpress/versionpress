<?php

/**
 * Plugin changes like installation, activation, deactivation etc.
 *
 * VP tags:
 *
 *     VP-Action: plugin/(install|activate|deactivate|update|edit|delete)/hello.php
 *     VP-Plugin-Name: Hello Dolly
 *
 * Note that the plugin identifier could be, and typically will be, something containing path separator,
 * e.g. `akismet/akismet.php`. So the full VP-Action tag will often look like:
 *
 *     VP-Action: plugin/install/akismet/akismet.php
 *
 */
class PluginChangeInfo extends TrackedChangeInfo {

    private static $OBJECT_TYPE = "plugin";
    const PLUGIN_NAME_TAG = "VP-Plugin-Name";

    /** @var string */
    private $pluginFile;

    /** @var string */
    private $pluginName;

    /** @var string */
    private $action;

    /**
     * @param string $pluginFile Something like "hello.php", or for plugins with their own folders, "akismet/akismet.php"
     * @param string $action See VP-Action tag documentation in the class docs
     * @param string $pluginName If not provided, finds the plugin name automatically based on $pluginFile
     */
    public function __construct($pluginFile, $action, $pluginName = null) {
        $this->pluginFile = $pluginFile;
        $this->action = $action;
        $this->pluginName = $pluginName ? $pluginName : $this->findPluginName();
    }

    public function getObjectType() {
        return self::$OBJECT_TYPE;
    }

    public function getAction() {
        return $this->action;
    }

    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $actionTag = $commitMessage->getVersionPressTag(TrackedChangeInfo::ACTION_TAG);
        $pluginName = $commitMessage->getVersionPressTag(self::PLUGIN_NAME_TAG);
        list( , $action, $pluginFile) = explode("/", $actionTag, 3);
        return new self($pluginFile, $action, $pluginName);
    }

    public function getChangeDescription() {
        return NStrings::capitalize($this->action) . (NStrings::endsWith($this->action, "e") ? "d" : "ed") . " plugin '{$this->pluginName}'";
    }

    protected function getActionTagValue() {
        return "{$this->getObjectType()}/{$this->getAction()}/" . $this->pluginFile;
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

    /**
     * Reports changes in files that relate to given ChangeInfo. Used in Committer
     * to commit only related files.
     * Returns data in this format:
     *
     * add  =>   [
     *             [ type => "storage-file",
     *               entity => "post",
     *               id => <VPID> ],
     *             [ type => "path",
     *               path => C:/www/wp/wp-content/upload/* ],
     *           ],
     * delete => [
     *             [ type => "storage-file",
     *               entity => "user",
     *               id => <VPID> ],
     *             ...
     *           ]
     *
     * @return array
     */
    public function getChangedFiles() {
        $changeType = $this->getAction() === "delete" ? "delete" : "add";

        $path = WP_CONTENT_DIR . "/plugins/";
        if (dirname($this->pluginFile) == ".") {
            // single-file plugin like hello.php
            $path .= $this->pluginFile;
        } else {
            // multifile plugin like akismet/...
            $path .= dirname($this->pluginFile) . "/*";
        }
        $pluginChange = array("type" => "path", "path" => $path);

        $optionChange = array("type" => "storage-file", "entity" => "options", "id" => "");

        return array($changeType => array($pluginChange, $optionChange));
    }
}
