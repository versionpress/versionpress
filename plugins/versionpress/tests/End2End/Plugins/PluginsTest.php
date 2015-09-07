<?php

namespace VersionPress\Tests\End2End\Plugins;

use Symfony\Component\Process\Process;
use VersionPress\Tests\End2End\Utils\End2EndTestCase;
use VersionPress\Tests\Utils\CommitAsserter;
use VersionPress\Tests\Utils\DBAsserter;

class PluginsTest extends End2EndTestCase {

    /** @var IPluginsTestWorker */
    private static $worker;

    /**
     * @see IPluginsTestWorker::setPluginInfo()
     * @var array
     */
    private static $pluginInfo;

    /**
     * Plugin info for bulk operations
     * @var array
     */
    private static $secondPluginInfo;

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();

        $testDataPath = __DIR__ . '/../test-data';
        self::$pluginInfo = array(
            'zipfile' => realpath($testDataPath . '/hello-dolly.1.6.zip'),
            'css-id' => 'hello-dolly',
            'name' => 'Hello Dolly',
            'affected-path' => 'hello-dolly/*',
        );

        self::$secondPluginInfo = array(
            'zipfile' => realpath($testDataPath . '/hello-dolly.1.6-2.zip'),
            'css-id' => 'hello-dolly-2',
            'name' => 'Hello Dolly 2',
            'affected-path' => 'hello-dolly-2/*',
        );

        self::$worker->setPluginInfo(self::$pluginInfo);
        self::$worker->setSecondPluginInfo(self::$secondPluginInfo);

        // possibly delete single-file Hello dolly
        try {
            self::$wpAutomation->runWpCliCommand('plugin', 'uninstall', array('hello'));
        } catch (\Exception $e) {
        }

        // possibly delete our testing plugins
        try {
            self::$wpAutomation->runWpCliCommand('plugin', 'uninstall', array('hello-dolly'));
            self::$wpAutomation->runWpCliCommand('plugin', 'uninstall', array('hello-dolly-2'));
        } catch (\Exception $e) {
        }

        $process = new Process("git add -A && git commit -m " . escapeshellarg("Plugin setup"), self::$testConfig->testSite->path);
        $process->run();
    }

    /**
     * @test
     * @testdox Uploading plugin creates 'plugin/install' action
     */
    public function uploadingPluginCreatesPluginInstallAction() {
        self::$worker->prepare_installPlugin();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->installPlugin();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("plugin/install");
        $commitAsserter->assertCommitTag("VP-Plugin-Name", self::$pluginInfo['name']);
        $commitAsserter->assertCommitPath("A", "wp-content/plugins/" . self::$pluginInfo['affected-path']);
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Activating plugin creates 'plugin/activate' action
     * @depends uploadingPluginCreatesPluginInstallAction
     */
    public function activatingPluginCreatesPluginActivateAction() {
        self::$worker->prepare_activatePlugin();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->activatePlugin();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("plugin/activate");
        $commitAsserter->assertCommitTag("VP-Plugin-Name", self::$pluginInfo['name']);
        $commitAsserter->assertCommitPath("M", "%vpdb%/options.ini");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Deactivating plugin creates 'plugin/deactivate' action
     * @depends activatingPluginCreatesPluginActivateAction
     */
    public function deactivatingPluginCreatesPluginDeactivateAction() {
        self::$worker->prepare_deactivatePlugin();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->deactivatePlugin();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("plugin/deactivate");
        $commitAsserter->assertCommitTag("VP-Plugin-Name", self::$pluginInfo['name']);
        $commitAsserter->assertCommitPath("M", "%vpdb%/options.ini");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Deleting plugin creates 'plugin/delete' action
     * @depends deactivatingPluginCreatesPluginDeactivateAction
     */
    public function deletingPluginCreatesPluginDeleteAction() {
        self::$worker->prepare_deletePlugin();
        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->deletePlugin();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("plugin/delete");
        $commitAsserter->assertCommitTag("VP-Plugin-Name", self::$pluginInfo['name']);
        $commitAsserter->assertCommitPath("D", "wp-content/plugins/" . self::$pluginInfo['affected-path']);
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Installing two plugins creates a bulk action
     */
    public function installingTwoPluginsCreatesBulkAction() {
        self::$worker->prepare_installTwoPlugins();
        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->installTwoPlugins();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertBulkAction('plugin/install', 2);
        $commitAsserter->assertCommitPath("A", "wp-content/plugins/" . self::$pluginInfo['affected-path']);
        $commitAsserter->assertCommitPath("A", "wp-content/plugins/" . self::$secondPluginInfo['affected-path']);
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Activation of two plugins creates a bulk action
     */
    public function activatingTwoPluginsCreatesBulkAction() {
        self::$worker->prepare_activateTwoPlugins();
        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->activateTwoPlugins();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertBulkAction('plugin/activate', 2);
        $commitAsserter->assertCommitPath("M", "%vpdb%/options.ini");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Deactivation of two plugins creates a bulk action
     */
    public function deactivatingTwoPluginsCreatesBulkAction() {
        self::$worker->prepare_deactivateTwoPlugins();
        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->deactivateTwoPlugins();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertBulkAction('plugin/deactivate', 2);
        $commitAsserter->assertCommitPath("M", "%vpdb%/options.ini");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Uninstalling two plugins creates a bulk action
     */
    public function uninstallingTwoPluginsCreatesBulkAction() {
        self::$worker->prepare_uninstallTwoPlugins();
        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->uninstallTwoPlugins();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertBulkAction('plugin/delete', 2);
        $commitAsserter->assertCommitPath("D", "wp-content/plugins/" . self::$pluginInfo['affected-path']);
        $commitAsserter->assertCommitPath("D", "wp-content/plugins/" . self::$secondPluginInfo['affected-path']);
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }
}