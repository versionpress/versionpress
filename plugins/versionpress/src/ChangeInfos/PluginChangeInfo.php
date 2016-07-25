<?php

namespace VersionPress\ChangeInfos;

use Nette\Utils\Strings;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Git\ActionsInfo;
use VersionPress\Git\CommitMessage;
use VersionPress\Utils\StringUtils;

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
class PluginChangeInfo extends TrackedChangeInfo
{

    private static $OBJECT_TYPE = "plugin";
    const PLUGIN_NAME_TAG = "VP-Plugin-Name";

    /** @var string */
    private $pluginFile;

    /** @var string */
    private $pluginName;

    /** @var string */
    private $action;

    /**
     * @param string $pluginFile Something like "hello.php" or for plugins with their own folders "akismet/akismet.php"
     * @param string $action See VP-Action tag documentation in the class docs
     * @param string $pluginName If not provided, finds the plugin name automatically based on $pluginFile
     */
    public function __construct($pluginFile, $action, $pluginName = null)
    {
        $this->pluginFile = $pluginFile;
        $this->action = $action;
        $this->pluginName = $pluginName ? $pluginName : $this->findPluginName();
    }

    public function getScope()
    {
        return self::$OBJECT_TYPE;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getChangeDescription()
    {
        return Strings::capitalize(StringUtils::verbToPastTense($this->action)) . " plugin '{$this->pluginName}'";
    }

    protected function getActionTagValue()
    {
        return "{$this->getScope()}/{$this->getAction()}/" . $this->pluginFile;
    }

    public function getCustomTags()
    {
        return [
            self::PLUGIN_NAME_TAG => $this->pluginName
        ];
    }

    private function findPluginName()
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = \get_plugins();
        return $plugins[$this->pluginFile]["Name"];
    }

    public function getChangedFiles()
    {
        $pluginPath = WP_PLUGIN_DIR . "/";
        if (dirname($this->pluginFile) == ".") {
            // single-file plugin like hello.php
            $pluginPath .= $this->pluginFile;
        } else {
            // multi-file plugin like akismet/...
            $pluginPath .= dirname($this->pluginFile) . "/*";
        }

        $pluginChange = ["type" => "path", "path" => $pluginPath];
        $optionChange = ["type" => "path", "path" => VP_VPDB_DIR];
        $composerChanges = [
            ["type" => "path", "path" => VP_PROJECT_ROOT . '/composer.json'],
            ["type" => "path", "path" => VP_PROJECT_ROOT . '/composer.lock'],
        ];

        return array_merge([$pluginChange, $optionChange], $composerChanges);
    }
}
