<?php

namespace VersionPress\Tests\SynchronizerTests;

use VersionPress\Database\DbSchemaInfo;
use VersionPress\Storages\StorageFactory;
use VersionPress\Tests\Automation\WpAutomation;
use VersionPress\Tests\Utils\DBAsserter;
use VersionPress\Tests\Utils\TestConfig;
use VersionPress\Utils\FileSystem;

class SynchronizerTestCase extends \PHPUnit_Framework_TestCase {

    /** @var DbSchemaInfo */
    protected static $schemaInfo;
    /** @var TestConfig */
    protected static $testConfig;
    /** @var StorageFactory */
    protected static $storageFactory;
    /** @var \wpdb */
    protected static $wpdb;

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        self::$testConfig = TestConfig::createDefaultConfig();

        self::assertVersionPressIsActive();
        DBAsserter::assertFilesEqualDatabase();

        $schemaReflection = new \ReflectionClass('VersionPress\Database\DbSchemaInfo');
        $schemaFile = dirname($schemaReflection->getFileName()) . '/wordpress-schema.neon';
        self::$schemaInfo = new DbSchemaInfo($schemaFile, self::$testConfig->testSite->dbTablePrefix);

        $vpdbPath = self::$testConfig->testSite->path . '/wp-content/vpdb';
        self::$storageFactory = new StorageFactory($vpdbPath, self::$schemaInfo);


        $dbHost = self::$testConfig->testSite->dbHost;
        $dbUser = self::$testConfig->testSite->dbUser;
        $dbPassword = self::$testConfig->testSite->dbPassword;
        $dbName = self::$testConfig->testSite->dbName;
        self::$wpdb = new \wpdb($dbUser, $dbPassword, $dbName, $dbHost);
    }

    private static function assertVersionPressIsActive() {
        $wpAutomation = new WpAutomation(self::$testConfig->testSite);
        if (!$wpAutomation->isSiteSetUp() || !$wpAutomation->isVersionPressInitialized()) {
            throw new \PHPUnit_Framework_AssertionFailedError("Synchronizer tests can be run only on WP site with initialized VersionPress");
        }
    }

}