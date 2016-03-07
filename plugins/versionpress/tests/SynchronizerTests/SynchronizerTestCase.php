<?php

namespace VersionPress\Tests\SynchronizerTests;

use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\ExtendedWpdb;
use VersionPress\Storages\StorageFactory;
use VersionPress\Tests\Automation\WpAutomation;
use VersionPress\Tests\Utils\DBAsserter;
use VersionPress\Tests\Utils\TestConfig;
use VersionPress\Utils\Process;
use VersionPress\Utils\AbsoluteUrlReplacer;

class SynchronizerTestCase extends \PHPUnit_Framework_TestCase {

    /** @var DbSchemaInfo */
    protected static $schemaInfo;
    /** @var TestConfig */
    protected static $testConfig;
    /** @var StorageFactory */
    protected static $storageFactory;
    /** @var \wpdb */
    protected static $wpdb;
    /** @var AbsoluteUrlReplacer */
    protected static $urlReplacer;

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        self::$testConfig = TestConfig::createDefaultConfig();

        self::setUpSite();
        DBAsserter::assertFilesEqualDatabase();

        $schemaReflection = new \ReflectionClass('VersionPress\Database\DbSchemaInfo');
        $schemaFile = dirname($schemaReflection->getFileName()) . '/wordpress-schema.yml';
        /** @var $wp_db_version */
        require(self::$testConfig->testSite->path . '/wp-includes/version.php');

        self::$schemaInfo = new DbSchemaInfo($schemaFile, self::$testConfig->testSite->dbTablePrefix, $wp_db_version);

        $dbHost = self::$testConfig->testSite->dbHost;
        $dbUser = self::$testConfig->testSite->dbUser;
        $dbPassword = self::$testConfig->testSite->dbPassword;
        $dbName = self::$testConfig->testSite->dbName;
        self::$wpdb = new ExtendedWpdb($dbUser, $dbPassword, $dbName, $dbHost);
        self::$wpdb->set_prefix(self::$testConfig->testSite->dbTablePrefix);

        $vpdbPath = self::$testConfig->testSite->path . '/wp-content/vpdb';
        self::$storageFactory = new StorageFactory($vpdbPath, self::$schemaInfo, self::$wpdb, array());
        self::$urlReplacer = new AbsoluteUrlReplacer(self::$testConfig->testSite->url);
    }

    private static function setUpSite() {
        $wpAutomation = new WpAutomation(self::$testConfig->testSite, self::$testConfig->wpCliVersion);
        if (!$wpAutomation->isSiteSetUp()) {
            $wpAutomation->setUpSite();
        }
        if (!$wpAutomation->isVersionPressInitialized()) {
            $wpAutomation->copyVersionPressFiles();
            $wpAutomation->initializeVersionPress();
        }
    }

    public static function tearDownAfterClass() {
        $process = new Process("git add -A && git commit -m " . escapeshellarg("Commited changes made by " . get_called_class()), self::$testConfig->testSite->path);
        $process->run();
    }

}
