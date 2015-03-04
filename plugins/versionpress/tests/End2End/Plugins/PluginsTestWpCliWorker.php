<?php

namespace VersionPress\Tests\End2End\Plugins;

use VersionPress\Tests\End2End\Utils\WpCliWorker;

class PluginsTestWpCliWorker extends WpCliWorker implements IPluginsTestWorker {

    private $pluginInfo;

    public function setPluginInfo($pluginInfo) {
        $this->pluginInfo = $pluginInfo;
        $this->pluginInfo['zipfile'] = $this->getRelativePath($this->testConfig->testSite->path, $this->pluginInfo['zipfile']);
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

    private function getRelativePath($from, $to) {
        // some compatibility fixes for Windows paths
        $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
        $to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
        $from = str_replace('\\', '/', $from);
        $to   = str_replace('\\', '/', $to);

        $from     = explode('/', $from);
        $to       = explode('/', $to);
        $relPath  = $to;

        foreach($from as $depth => $dir) {
            // find first non-matching dir
            if($dir === $to[$depth]) {
                // ignore this directory
                array_shift($relPath);
            } else {
                // get number of remaining dirs to $from
                $remaining = count($from) - $depth;
                if($remaining > 1) {
                    // add traversals up to first matching dir
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath = array_pad($relPath, $padLength, '..');
                    break;
                } else {
                    $relPath[0] = './' . $relPath[0];
                }
            }
        }
        return implode('/', $relPath);
    }
}