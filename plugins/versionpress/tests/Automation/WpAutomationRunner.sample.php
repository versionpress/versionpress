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

        $wpAutomation->runWpCliCommand('vp', 'config', ['VP_PROJECT_ROOT', '.']);
    }

    /**
     * @test
     */
    public function runDBAsserter()
    {
        DBAsserter::assertFilesEqualDatabase();
    }
}
