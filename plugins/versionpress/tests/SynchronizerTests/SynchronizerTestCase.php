<?php

namespace VersionPress\Tests\SynchronizerTests;

use VersionPress\Actions\ActionsInfo;
use VersionPress\Actions\ActionsInfoProvider;
use VersionPress\ChangeInfos\ChangeInfoFactory;
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
    /** @var WpAutomation */
    private static $wpAutomation;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$testConfig = TestConfig::createDefaultConfig();
        self::$wpAutomation = new WpAutomation(self::$testConfig->testSite, self::$testConfig->wpCliVersion);

        self::setUpSite();
        DBAsserter::assertFilesEqualDatabase();

        $yamlDir = self::$wpAutomation->getPluginsDir() . '/versionpress/.versionpress';
        $schemaReflection = new \ReflectionClass(DbSchemaInfo::class);
        $schemaFile = $yamlDir . '/schema.yml';
        $actionsFile = $yamlDir . '/actions.yml';
        $shortcodeFile = dirname($schemaReflection->getFileName()) . '/wordpress-shortcodes.yml';

        /** @var $wp_db_version */
        require(self::$wpAutomation->getAbspath() . '/wp-includes/version.php');

        if (!function_exists('get_shortcode_regex')) {
            require_once(self::$wpAutomation->getAbspath() . '/wp-includes/shortcodes.php');
        }

        self::$schemaInfo = new DbSchemaInfo([$schemaFile], self::$testConfig->testSite->dbTablePrefix, $wp_db_version);
        $actionsInfoProvider = new ActionsInfoProvider([$actionsFile]);

        $changeInfoFactory = new ChangeInfoFactory(self::$schemaInfo, $actionsInfoProvider);

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

        $vpdbPath = self::$wpAutomation->getVpdbDir();
        self::$storageFactory = new StorageFactory($vpdbPath, self::$schemaInfo, self::$database, [], $changeInfoFactory);
        self::$urlReplacer = new AbsoluteUrlReplacer(self::$testConfig->testSite->url);
    }

    private static function setUpSite()
    {
        if (!self::$wpAutomation->isSiteSetUp()) {
            self::$wpAutomation->setUpSite();
        }
        if (!self::$wpAutomation->isVersionPressInitialized()) {
            self::$wpAutomation->copyVersionPressFiles();
            self::$wpAutomation->initializeVersionPress();
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
