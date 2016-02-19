<?php
namespace VersionPress\Tests\LoadTests;

use DateTime;
use VersionPress\Git\GitConfig;
use VersionPress\Git\GitRepository;
use VersionPress\Git\MergeDriverInstaller;
use VersionPress\Tests\Utils\MergeDriverTestUtils;
use VersionPress\Utils\FileSystem;
use VersionPress\Utils\IniSerializer;
use VersionPress\Utils\Process;
use VersionPress\Utils\StringUtils;

class MergeDriverLoadTest extends \PHPUnit_Framework_TestCase {

    private static $repositoryDir;

    private static $initializationDir;

    public static function setUpBeforeClass() {
        self::$initializationDir = '../../src/Initialization';
        self::$repositoryDir = __DIR__ . '/repository';

        define('VERSIONPRESS_PLUGIN_DIR', self::$repositoryDir); // fake
        define('VERSIONPRESS_MIRRORING_DIR', self::$repositoryDir); // fake
        define('VP_PROJECT_ROOT', self::$repositoryDir); // fake
        define('BRANCH_NAME', 'test-branch');

        define('CHECKOUT_BRANCH_CMD', 'git checkout -b ' . BRANCH_NAME);
        define('CHECKOUT_MASTER_CMD', 'git checkout master');
        define('MERGE_CMD', 'git merge ' . BRANCH_NAME);

        define('ORIGIN_DATE', '10-02-16 08:00:00');
        define('MASTER_DATE', '15-02-16 12:00:11');
        define('BRANCH_DATE', '17-02-16 19:19:23');

    }

    public function setUp() {
        MergeDriverTestUtils::initRepository(self::$repositoryDir);
    }

    public function tearDown() {
        MergeDriverTestUtils::destroyRepository();
    }

    public static function tearDownAfterClass() {
        MergeDriverTestUtils::destroyRepository();
    }

    /**
     * @test
     */
    public function phpDriverLoadTest() {

        MergeDriverTestUtils::installMergeDriver(self::$initializationDir);
        MergeDriverTestUtils::switchDriverToPhp();
        $this->runLoadTest();
        $time_start = microtime(true);
        $mergeEC = MergeDriverTestUtils::runProcess(MERGE_CMD);
        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start);
        echo 'Php Execution Time: '.$execution_time.' Sec';
        $this->assertEquals(0, $mergeEC);

    }

    /**
     * @test
     */
    public function bashDriverLoadTest() {

        MergeDriverTestUtils::installMergeDriver(self::$initializationDir);
        MergeDriverTestUtils::switchDriverToBash();
        $this->runLoadTest();
        $time_start = microtime(true);
        $mergeEC = MergeDriverTestUtils::runProcess(MERGE_CMD);
        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start);
        echo 'Bash Execution Time: '.$execution_time.' Sec';
        $this->assertEquals(0, $mergeEC);
    }

    private function runLoadTest() {
        $limit = 1000;
        for ($i = 0; $i < $limit; $i++) {
            MergeDriverTestUtils::fillFakeFile(ORIGIN_DATE, 'file' . $i . '.ini');
        }
        MergeDriverTestUtils::commit('Initial commit to Ancestor');
        MergeDriverTestUtils::runProcess(CHECKOUT_BRANCH_CMD);
        for ($i = 0; $i < $limit; $i++) {
            MergeDriverTestUtils::fillFakeFile(BRANCH_DATE, 'file' . $i . '.ini');
        }
        MergeDriverTestUtils::commit('Commit to branch');
        MergeDriverTestUtils::runProcess(CHECKOUT_MASTER_CMD);
        for ($i = 0; $i < $limit; $i++) {
            MergeDriverTestUtils::fillFakeFile(MASTER_DATE, 'file' . $i . '.ini');
        }
        MergeDriverTestUtils::commit('Commit to master');
        echo "Done";
    }
}
