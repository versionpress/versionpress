<?php

namespace VersionPress\Tests\End2End\Plugins;

use VersionPress\Tests\End2End\Utils\SeleniumWorker;

class PluginsTestSeleniumWorker extends SeleniumWorker implements IPluginsTestWorker {

    private static $pluginInfo;

    public function setPluginInfo($pluginInfo) {
        self::$pluginInfo = $pluginInfo;
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
}