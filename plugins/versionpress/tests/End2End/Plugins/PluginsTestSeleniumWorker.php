<?php

namespace VersionPress\Tests\End2End\Plugins;

use VersionPress\Tests\End2End\Utils\PathUtils;
use VersionPress\Tests\End2End\Utils\SeleniumWorker;

class PluginsTestSeleniumWorker extends SeleniumWorker implements IPluginsTestWorker {

    private static $pluginInfo;
    private static $secondPluginInfo;

    public function setPluginInfo($pluginInfo) {
        self::$pluginInfo = $pluginInfo;
    }

    public function setSecondPluginInfo($secondPluginInfo) {
        self::$secondPluginInfo = $secondPluginInfo;
    }

    public function prepare_installPlugin() {
        $this->url('wp-admin/plugin-install.php?tab=upload');
    }

    public function installPlugin() {
        $this->byCssSelector('#pluginzip')->value(self::$pluginInfo['zipfile']);
        $this->byCssSelector('#install-plugin-submit')->click();
        $this->waitAfterRedirect();
    }

    public function prepare_activatePlugin() {
        $this->url("wp-admin/plugins.php");
    }

    public function activatePlugin() {
        $this->byCssSelector("#" . self::$pluginInfo['css-id'] . " .activate a")->click();
        $this->waitAfterRedirect();
    }

    public function prepare_deactivatePlugin() {
        $this->url("wp-admin/plugins.php");
    }

    public function deactivatePlugin() {
        $this->byCssSelector("#" . self::$pluginInfo['css-id'] . " .deactivate a")->click();
        $this->waitAfterRedirect();
    }

    public function prepare_deletePlugin() {
    }

    public function deletePlugin() {
        $this->byCssSelector("#" . self::$pluginInfo['css-id'] . " .delete a")->click();
        $this->waitAfterRedirect();
        $this->byCssSelector("#submit")->click();
        $this->waitAfterRedirect();
    }

    public function prepare_installTwoPlugins() {
        throw new \PHPUnit_Framework_SkippedTestError('There is no way to install more plugins at once using selenium');
    }

    public function installTwoPlugins() {
    }

    public function prepare_activateTwoPlugins() {
        $plugin1Path = PathUtils::getRelativePath(self::$testConfig->testSite->path, self::$pluginInfo['zipfile']);
        $plugin2Path = PathUtils::getRelativePath(self::$testConfig->testSite->path, self::$secondPluginInfo['zipfile']);
        self::$wpAutomation->runWpCliCommand('plugin', 'install', array($plugin1Path, $plugin2Path));
        $this->url("wp-admin/plugins.php");
    }

    public function activateTwoPlugins() {
        $this->performBulkAction('activate-selected');
    }

    public function prepare_deactivateTwoPlugins() {
        $this->url("wp-admin/plugins.php");
    }

    public function deactivateTwoPlugins() {
        $this->performBulkAction('deactivate-selected');
    }

    public function prepare_uninstallTwoPlugins() {
        $this->url("wp-admin/plugins.php");
    }

    public function uninstallTwoPlugins() {
        $this->performBulkAction('delete-selected');
        $this->byId('submit')->click();
    }

    private function performBulkAction($action) {
        // select two plugins
        $this->byCssSelector('#' . self::$pluginInfo['css-id'] . ' .check-column input[type=checkbox]')->click();
        $this->byCssSelector('#' . self::$secondPluginInfo['css-id'] . ' .check-column input[type=checkbox]')->click();
        // choose bulk edit
        $this->select($this->byId('bulk-action-selector-top'))->selectOptionByValue($action);
        $this->jsClickAndWait('#doaction');
    }
}