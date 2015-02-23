<?php

namespace VersionPress\Tests\Automation;

use VersionPress\Tests\Utils\TestConfig;

/**
 * An example of how to run WpAutomation methods from PhpStorm via "unit tests".
 * Rename to WpAutomationRunner.local.php and customize as you wish.
 */
class WpAutomationRunnerSample extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function runAutomation() {
        $testConfig = new TestConfig(__DIR__ . '/../test-config.neon');
        $wpAutomation = new WpAutomation($testConfig->testSite);

        $wpAutomation->setUpSite();
        $wpAutomation->copyVersionPressFiles();
    }
}
