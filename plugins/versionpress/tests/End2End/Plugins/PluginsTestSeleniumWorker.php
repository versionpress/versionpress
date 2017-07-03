<?php

namespace VersionPress\Tests\End2End\Plugins;

use VersionPress\Tests\End2End\Utils\SeleniumWorker;
use VersionPress\Utils\PathUtils;

class PluginsTestSeleniumWorker extends SeleniumWorker implements IPluginsTestWorker
{

    private static $pluginInfo;
    private static $secondPluginInfo;

    public function setPluginInfo($pluginInfo)
    {
        self::$pluginInfo = $pluginInfo;
    }

    public function setSecondPluginInfo($secondPluginInfo)
    {
        self::$secondPluginInfo = $secondPluginInfo;
    }

    public function prepare_installPlugin()
    {
        $this->url(self::$wpAdminPath . '/plugin-install.php?tab=upload');
    }

    public function installPlugin()
    {
        $this->byCssSelector('#pluginzip')->value(self::$pluginInfo['zipfile']);
        $this->byCssSelector('#install-plugin-submit')->click();
        $this->waitAfterRedirect();
    }

    public function prepare_activatePlugin()
    {
        $this->url(self::$wpAdminPath . "/plugins.php");
    }

    public function activatePlugin()
    {
        $this->byCssSelector(".activate a[href*='" . self::$pluginInfo['url-fragment'] . "']")->click();
        $this->waitAfterRedirect();
    }

    public function prepare_deactivatePlugin()
    {
        $this->url(self::$wpAdminPath . "/plugins.php");
    }

    public function deactivatePlugin()
    {
        $this->byCssSelector(".deactivate a[href*='" . self::$pluginInfo['url-fragment'] . "']")->click();
        $this->waitAfterRedirect();
    }

    public function prepare_deletePlugin()
    {
    }

    public function deletePlugin()
    {
        $this->byCssSelector(".delete a[href*='" . self::$pluginInfo['url-fragment'] . "']")->click();

        if ($this->isWpVersionLowerThan('4.6')) {
            $this->waitAfterRedirect();
            $this->byCssSelector("#submit")->click();
            $this->waitAfterRedirect();
        } else {
            $this->acceptAlert();
            $this->waitForAjax();
        }
    }

    public function prepare_installTwoPlugins()
    {
        throw new \PHPUnit_Framework_SkippedTestError('There is no way to install more plugins at once using selenium');
    }

    public function installTwoPlugins()
    {
    }

    public function prepare_activateTwoPlugins()
    {
        $plugin1Path = PathUtils::getRelativePath(self::$testConfig->testSite->path, self::$pluginInfo['zipfile']);
        $plugin2Path = PathUtils::getRelativePath(
            self::$testConfig->testSite->path,
            self::$secondPluginInfo['zipfile']
        );
        self::$wpAutomation->runWpCliCommand('plugin', 'install', [$plugin1Path, $plugin2Path]);
        $this->url(self::$wpAdminPath . "/plugins.php");
    }

    public function activateTwoPlugins()
    {
        $this->performBulkAction('activate-selected');
    }

    public function prepare_deactivateTwoPlugins()
    {
        $this->url(self::$wpAdminPath . "/plugins.php");
    }

    public function deactivateTwoPlugins()
    {
        $this->performBulkAction('deactivate-selected');
    }

    public function prepare_uninstallTwoPlugins()
    {
        $this->url(self::$wpAdminPath . "/plugins.php");
    }

    public function uninstallTwoPlugins()
    {
        $this->performBulkAction('delete-selected');
        if ($this->isWpVersionLowerThan('4.7')) {
            $this->byId('submit')->click();
        } else {
            $this->acceptAlert();
            $this->waitForAjax();
        }
    }

    private function performBulkAction($action)
    {
        // select two plugins
        $this->byCssSelector('.check-column input[value*="' . self::$pluginInfo['url-fragment'] . '"]')->click();
        $this->byCssSelector('.check-column input[value*="' . self::$secondPluginInfo['url-fragment'] . '"]')->click();
        // choose bulk edit
        $this->select($this->byId('bulk-action-selector-top'))->selectOptionByValue($action);
        $this->byId('doaction')->click();
    }
}
