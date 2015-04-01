<?php

namespace VersionPress\Tests\End2End\Plugins;

use VersionPress\Tests\End2End\Utils\WpCliWorker;

class PluginsTestWpCliWorker extends WpCliWorker implements IPluginsTestWorker {

    private $pluginInfo;

    public function setPluginInfo($pluginInfo) {
        $this->pluginInfo = $pluginInfo;
        $this->pluginInfo['zipfile'] = $this->getRelativePath($this->pluginInfo['zipfile']);
    }

    public function prepare_installPlugin() {
    }

    public function installPlugin() {
        $this->wpAutomation->runWpCliCommand('plugin', 'install', array($this->pluginInfo['css-id']));
    }

    public function prepare_activatePlugin() {
    }

    public function activatePlugin() {
        $this->wpAutomation->runWpCliCommand('plugin', 'activate', array($this->pluginInfo['css-id']));
    }

    public function prepare_deactivatePlugin() {
    }

    public function deactivatePlugin() {
        $this->wpAutomation->runWpCliCommand('plugin', 'deactivate', array($this->pluginInfo['css-id']));
    }

    public function prepare_deletePlugin() {
    }

    public function deletePlugin() {
        $this->wpAutomation->runWpCliCommand('plugin', 'delete', array($this->pluginInfo['css-id']));
    }
}