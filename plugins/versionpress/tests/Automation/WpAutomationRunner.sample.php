<?php

namespace VersionPress\Tests\Automation;

use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\ExtendedWpdb;
use VersionPress\Storages\StorageFactory;
use VersionPress\Synchronizers\SynchronizationProcess;
use VersionPress\Synchronizers\SynchronizerFactory;
use VersionPress\Tests\Utils\DBAsserter;
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
        $testConfig = TestConfig::createDefaultConfig();
        $wpAutomation = new WpAutomation($testConfig->testSite, $testConfig->wpCliVersion);

        $wpAutomation->setUpSite();
        $wpAutomation->copyVersionPressFiles();
        $wpAutomation->activateVersionPress();
    }

    /**
     * @test
     */
    public function runDBAsserter() {
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     */
    public function runSynchronization() {
        $testConfig = TestConfig::createDefaultConfig();
        $schemaReflection = new \ReflectionClass('VersionPress\Database\DbSchemaInfo');
        $schemaFile = dirname($schemaReflection->getFileName()) . '/wordpress-schema.neon';
        $schemaInfo = new DbSchemaInfo($schemaFile, $testConfig->testSite->dbTablePrefix);

        $dbHost = $testConfig->testSite->dbHost;
        $dbUser = $testConfig->testSite->dbUser;
        $dbPassword = $testConfig->testSite->dbPassword;
        $dbName = $testConfig->testSite->dbName;
        $wpdb = new ExtendedWpdb($dbUser, $dbPassword, $dbName, $dbHost);
        $wpdb->set_prefix($testConfig->testSite->dbTablePrefix);

        $vpdbPath = $testConfig->testSite->path . '/wp-content/vpdb';
        $storageFactory = new StorageFactory($vpdbPath, $schemaInfo, $wpdb);
        $synchronizerFactory = new SynchronizerFactory($storageFactory, $wpdb, $schemaInfo);
        $synchronizationProcess = new SynchronizationProcess($synchronizerFactory);
        $synchronizationProcess->synchronize();
        DBAsserter::assertFilesEqualDatabase();
    }
}
