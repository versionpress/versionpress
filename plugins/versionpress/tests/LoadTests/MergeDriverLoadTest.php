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

    private static $branchName;

    private static $checkoutBranchCmd;

    private static $checkoutMasterCmd;

    private static $mergeCmd;

    private static $originDate;

    private static $masterDate;

    private static $branchDate;

    public static function setUpBeforeClass() {
        self::$initializationDir = '../../src/Initialization';
        self::$repositoryDir = __DIR__ . '/repository';

        define('VERSIONPRESS_PLUGIN_DIR', self::$repositoryDir); // fake
        define('VERSIONPRESS_MIRRORING_DIR', self::$repositoryDir); // fake
        define('VP_PROJECT_ROOT', self::$repositoryDir); // fake
        self::$branchName = 'test-branch';

        self::$checkoutBranchCmd = 'git checkout -b ' . self::$branchName;
        self::$checkoutMasterCmd = 'git checkout master';
        self::$mergeCmd = 'git merge ' . self::$branchName;

        self::$originDate = '10-02-16 08:00:00';
        self::$masterDate = '15-02-16 12:00:11';
        self::$branchDate = '17-02-16 19:19:23';

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

        MergeDriverInstaller::installMergeDriver(self::$initializationDir);
        MergeDriverTestUtils::switchDriverToPhp();
        $this->prepareTestData();
        $time_start = microtime(true);
        $mergeCommandExitCode = MergeDriverTestUtils::runProcess(self::$mergeCmd);
        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start);
        echo 'Php Execution Time: ' . $execution_time . " Sec\n";
        $this->assertEquals(0, $mergeCommandExitCode);

    }

    /**
     * @test
     */
    public function bashDriverLoadTest() {

        MergeDriverInstaller::installMergeDriver(self::$initializationDir);
        MergeDriverTestUtils::switchDriverToBash();
        $this->prepareTestData();
        $time_start = microtime(true);
        $mergeCommandExitCode = MergeDriverTestUtils::runProcess(self::$mergeCmd);
        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start);
        echo 'Bash Execution Time: ' . $execution_time . " Sec\n";
        $this->assertEquals(0, $mergeCommandExitCode);
    }

    private function prepareTestData() {
        $limit = 1000;
        for ($i = 0; $i < $limit; $i++) {
            MergeDriverTestUtils::fillFakeFile(self::$originDate, 'file' . $i . '.ini');
        }
        MergeDriverTestUtils::commit('Initial commit to Ancestor');
        MergeDriverTestUtils::runProcess(self::$checkoutBranchCmd);
        for ($i = 0; $i < $limit; $i++) {
            MergeDriverTestUtils::fillFakeFile(self::$branchDate, 'file' . $i . '.ini');
        }
        MergeDriverTestUtils::commit('Commit to branch');
        MergeDriverTestUtils::runProcess(self::$checkoutMasterCmd);
        for ($i = 0; $i < $limit; $i++) {
            MergeDriverTestUtils::fillFakeFile(self::$masterDate, 'file' . $i . '.ini');
        }
        MergeDriverTestUtils::commit('Commit to master');
    }
}
