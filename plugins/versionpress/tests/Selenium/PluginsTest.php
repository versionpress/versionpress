<?php

namespace VersionPress\Tests\Selenium;

use Exception;
use VersionPress\Tests\Utils\CommitAsserter;

class PluginsTest extends SeleniumTestCase {

    /**
     * Required keys are:
     * zipfile: absolute path used for upload
     * css-id: id of row representing given plugin on 'wp-admin/plugins.php' page
     * name: name of plugin (saved in VP-Plugin-Name tag)
     * affected-path: directory or file that should be affected by installation / deleting
     *
     * @var array
     */
    private static $pluginInfo;


    public static function setupBeforeClass() {

        parent::setUpBeforeClass();

        $testDataPath = __DIR__ . DIRECTORY_SEPARATOR . 'test-data' . DIRECTORY_SEPARATOR;
        self::$pluginInfo = array(
            'zipfile' => $testDataPath . 'hello-dolly.1.6.zip',
            'css-id' => 'hello-dolly',
            'name' => 'Hello Dolly',
            'affected-path' => 'hello-dolly/*',
        );

        // possibly delete single-file Hello dolly
        try {
            self::$wpAutomation->runWpCliCommand('plugin', 'uninstall', array('hello'));
        } catch (Exception $e) {
        }

        // possibly delete our testing plugin
        try {
            self::$wpAutomation->runWpCliCommand('plugin', 'uninstall', array('hello-dolly'));
        } catch (Exception $e) {
        }

        $process = new \Symfony\Component\Process\Process("git add -A && git commit -m " . escapeshellarg("Plugin setup"), self::$testConfig->testSite->path);
        $process->run();

    }

    /**
     * @test
     * @testdox Uploading plugin creates 'plugin/install' action
     */
    public function uploadingPluginCreatesPluginInstallAction() {
        $this->url('wp-admin/plugin-install.php?tab=upload');

        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->byCssSelector('#pluginzip')->value(self::$pluginInfo['zipfile']);
        $this->byCssSelector('#install-plugin-submit')->click();
        $this->waitAfterRedirect();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("plugin/install");
        $commitAsserter->assertCommitTag("VP-Plugin-Name", self::$pluginInfo['name']);
        $commitAsserter->assertCommitPath("A", "wp-content/plugins/" . self::$pluginInfo['affected-path']);
        $commitAsserter->assertCleanWorkingDirectory();
    }

    /**
     * @test
     * @testdox Activating plugin creates 'plugin/activate' action
     * @depends uploadingPluginCreatesPluginInstallAction
     */
    public function activatingPluginCreatesPluginActivateAction() {
        $this->url("wp-admin/plugins.php");
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->byCssSelector("#" . self::$pluginInfo['css-id'] . " .activate a")->click();
        $this->waitAfterRedirect();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("plugin/activate");
        $commitAsserter->assertCommitTag("VP-Plugin-Name", self::$pluginInfo['name']);
        $commitAsserter->assertCommitPath("M", "%vpdb%/options.ini");
        $commitAsserter->assertCleanWorkingDirectory();
    }

    /**
     * @test
     * @testdox Deactivating plugin creates 'plugin/deactivate' action
     * @depends activatingPluginCreatesPluginActivateAction
     */
    public function deactivatingPluginCreatesPluginDeactivateAction() {
        $this->url("wp-admin/plugins.php");
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->byCssSelector("#" . self::$pluginInfo['css-id'] . " .deactivate a")->click();
        $this->waitAfterRedirect();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("plugin/deactivate");
        $commitAsserter->assertCommitTag("VP-Plugin-Name", self::$pluginInfo['name']);
        $commitAsserter->assertCommitPath("M", "%vpdb%/options.ini");
        $commitAsserter->assertCleanWorkingDirectory();
    }

    /**
     * @test
     * @testdox Deleting plugin creates 'plugin/delete' action
     * @depends deactivatingPluginCreatesPluginDeactivateAction
     */
    public function deletingPluginCreatesPluginDeleteAction() {

        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->byCssSelector("#" . self::$pluginInfo['css-id'] . " .delete a")->click();
        $this->waitAfterRedirect();
        $this->byCssSelector("#submit")->click();
        $this->waitAfterRedirect();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("plugin/delete");
        $commitAsserter->assertCommitTag("VP-Plugin-Name", self::$pluginInfo['name']);
        $commitAsserter->assertCommitPath("D", "wp-content/plugins/" . self::$pluginInfo['affected-path']);
        $commitAsserter->assertCleanWorkingDirectory();
    }

}
