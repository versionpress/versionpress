<?php

namespace VersionPress\Tests\Automation;

use VersionPress\Tests\Utils\DBAsserter;
use VersionPress\Tests\Utils\TestConfig;
use VersionPress\Utils\FileSystem;
use VersionPress\Utils\Process;

/**
 * An example of how to run WpAutomation methods from PhpStorm via "unit tests".
 * Rename to WpAutomationRunner.local.php and customize as you wish.
 */
class WpAutomationRunnerSample extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function runAutomation()
    {
        $testConfig = TestConfig::createDefaultConfig();
        $wpAutomation = new WpAutomation($testConfig->testSite, $testConfig->wpCliVersion);

        $wpAutomation->setUpSite();
        $wpAutomation->copyVersionPressFiles();
        $wpAutomation->activateVersionPress();
    }

    /**
     * @test
     */
    public function createPedestalBasedSite()
    {
        $testConfig = TestConfig::createDefaultConfig();

        $testConfig->testSite->url .= '/web';
        $wpAutomation = new WpAutomation($testConfig->testSite, $testConfig->wpCliVersion);

        FileSystem::remove($testConfig->testSite->path);
        FileSystem::mkdir($testConfig->testSite->path);

        $process = new Process('composer create-project -s dev versionpress/pedestal .', $testConfig->testSite->path);
        $process->run();

        function updateConfigConstant(WpAutomation $wpAutomation, $constant, $value, $variable = false)
        {
            $vpInternalCommandFile = __DIR__ . '/../../src/Cli/vp-internal.php';
            $wpAutomation->runWpCliCommand(
                'vp-internal',
                'update-config',
                [$constant, $value, 'require' => $vpInternalCommandFile]
            );
        }

        updateConfigConstant($wpAutomation, 'DB_NAME', $testConfig->testSite->dbName);
        updateConfigConstant($wpAutomation, 'DB_USER', $testConfig->testSite->dbUser);
        updateConfigConstant($wpAutomation, 'DB_PASSWORD', $testConfig->testSite->dbPassword);
        updateConfigConstant($wpAutomation, 'DB_HOST', $testConfig->testSite->dbHost);
        updateConfigConstant($wpAutomation, 'WP_HOME', $testConfig->testSite->url);

        $wpAutomation->runWpCliCommand('db', 'drop', ['yes' => null]);
        $wpAutomation->runWpCliCommand('db', 'create');

        $wpAutomation->installWordPress();
        $wpAutomation->copyVersionPressFiles();
        $wpAutomation->activateVersionPress();
    }

    /**
     * @test
     */
    public function runDBAsserter()
    {
        DBAsserter::assertFilesEqualDatabase();
    }
}
