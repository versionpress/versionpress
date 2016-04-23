<?php

namespace VersionPress\Tests\SynchronizerTests;

use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\ShortcodesInfo;
use VersionPress\Database\ShortcodesReplacer;
use VersionPress\Database\VpidRepository;
use VersionPress\Storages\StorageFactory;
use VersionPress\Tests\Automation\WpAutomation;
use VersionPress\Tests\Utils\DBAsserter;
use VersionPress\Tests\Utils\TestConfig;
use VersionPress\Utils\AbsoluteUrlReplacer;
use VersionPress\Utils\Process;
use wpdb;

class SynchronizerTestCase extends \PHPUnit_Framework_TestCase
{

    /** @var DbSchemaInfo */
    protected static $schemaInfo;
    /** @var TestConfig */
    protected static $testConfig;
    /** @var StorageFactory */
    protected static $storageFactory;
    /** @var Database */
    protected static $database;
    /** @var AbsoluteUrlReplacer */
    protected static $urlReplacer;
    /** @var ShortcodesReplacer */
    protected static $shortcodesReplacer;
    /** @var VpidRepository */
    protected static $vpidRepository;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$testConfig = TestConfig::createDefaultConfig();

        self::setUpSite();
        DBAsserter::assertFilesEqualDatabase();

        $schemaReflection = new \ReflectionClass(DbSchemaInfo::class);
        $schemaFile = dirname($schemaReflection->getFileName()) . '/wordpress-schema.yml';
        $shortcodeFile = dirname($schemaReflection->getFileName()) . '/wordpress-shortcodes.yml';

        /** @var $wp_db_version */
        require(self::$testConfig->testSite->path . '/wp-includes/version.php');

        if (!function_exists('get_shortcode_regex')) {
            require_once(self::$testConfig->testSite->path . '/wp-includes/shortcodes.php');
        }

        self::$schemaInfo = new DbSchemaInfo($schemaFile, self::$testConfig->testSite->dbTablePrefix, $wp_db_version);

        $dbHost = self::$testConfig->testSite->dbHost;
        $dbUser = self::$testConfig->testSite->dbUser;
        $dbPassword = self::$testConfig->testSite->dbPassword;
        $dbName = self::$testConfig->testSite->dbName;
        $wpdb = new wpdb($dbUser, $dbPassword, $dbName, $dbHost);
        $wpdb->set_prefix(self::$testConfig->testSite->dbTablePrefix);
        self::$database = new Database($wpdb);

        $shortcodesInfo = new ShortcodesInfo($shortcodeFile);
        self::$vpidRepository = new VpidRepository(self::$database, self::$schemaInfo);
        self::$shortcodesReplacer = new ShortcodesReplacer($shortcodesInfo, self::$vpidRepository);

        $vpdbPath = self::$testConfig->testSite->path . '/wp-content/vpdb';
        self::$storageFactory = new StorageFactory($vpdbPath, self::$schemaInfo, self::$database, []);
        self::$urlReplacer = new AbsoluteUrlReplacer(self::$testConfig->testSite->url);
    }

    private static function setUpSite()
    {
        $wpAutomation = new WpAutomation(self::$testConfig->testSite, self::$testConfig->wpCliVersion);
        if (!$wpAutomation->isSiteSetUp()) {
            $wpAutomation->setUpSite();
        }
        if (!$wpAutomation->isVersionPressInitialized()) {
            $wpAutomation->copyVersionPressFiles();
            $wpAutomation->initializeVersionPress();
        }
    }

    public static function tearDownAfterClass()
    {
        $process = new Process(
            "git add -A && git commit -m " . escapeshellarg("Commited changes made by " . get_called_class()),
            self::$testConfig->testSite->path
        );
        $process->run();
    }
}
