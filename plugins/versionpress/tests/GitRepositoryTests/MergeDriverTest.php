<?php
namespace VersionPress\Tests\GitRepositoryTests;

use DateTime;
use VersionPress\Git\GitConfig;
use VersionPress\Git\GitRepository;
use VersionPress\Git\MergeDriverInstaller;
use VersionPress\Tests\Utils\MergeDriverTestUtils;
use VersionPress\Utils\FileSystem;
use VersionPress\Utils\IniSerializer;
use VersionPress\Utils\Process;
use VersionPress\Utils\StringUtils;

class MergeDriverTest extends \PHPUnit_Framework_TestCase {


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
    public function isMergeDriverInstalledTest() {
        MergeDriverInstaller::installMergeDriver(self::$initializationDir);
        $this->assertContains('vp-ini', file_get_contents(self::$repositoryDir . "/.git/config"));
        $this->assertContains('merge=vp-ini', file_get_contents(self::$repositoryDir . "/.gitattributes"));
    }

    /**
     * @test
     */
    public function uninstallMergeDriverTest() {
        MergeDriverInstaller::installMergeDriver(self::$initializationDir);
        MergeDriverInstaller::uninstallMergeDriver();
        $this->assertNotContains('vp-ini', file_get_contents(self::$repositoryDir . "/.git/config"));
        $this->assertNotContains('merge=vp-ini', file_get_contents(self::$repositoryDir . "/.gitattributes"));
    }

    /**
     * @test
     */
    public function isBashMergedWithoutConflictTest() {

        MergeDriverInstaller::installMergeDriver(self::$initializationDir);
        MergeDriverTestUtils::switchDriverToBash();

        $this->prepareNonConflictingData();

        $this->assertEquals(0, MergeDriverTestUtils::runProcess(self::$mergeCmd), 'Merge returned unexpected exit code.');

    }

    /**
     * @test
     */
    public function isBashMergedWithoutConflictInDateTest() {

        MergeDriverInstaller::installMergeDriver(self::$initializationDir);
        MergeDriverTestUtils::switchDriverToBash();

        $this->prepareConflictingData();

        $this->assertEquals(1, MergeDriverTestUtils::runProcess(self::$mergeCmd));

        copy(self::$repositoryDir . '/file.ini', '/Users/Ivan/expected-merge-conflict.ini');
        $expected = file_get_contents(__DIR__ . '/expected-merge-conflict.ini');
        $file = file_get_contents(self::$repositoryDir . '/file.ini');
        $this->assertEquals($expected, $file, 'Merge returned unexpected exit code.');

    }

    /**
     * @test
     */
    public function isPhpMergedWithoutConflictTest() {
        MergeDriverInstaller::installMergeDriver(self::$initializationDir);
        MergeDriverTestUtils::switchDriverToPhp();

        $this->prepareNonConflictingData();

        $this->assertEquals(0, MergeDriverTestUtils::runProcess(self::$mergeCmd), 'Merge returned unexpected exit code.');

    }

    /**
     * @test
     */
    public function isPhpMergedWithoutConflictInDateTest() {

        MergeDriverInstaller::installMergeDriver(self::$initializationDir);
        MergeDriverTestUtils::switchDriverToPhp();

        $this->prepareConflictingData();

        $this->assertEquals(1, MergeDriverTestUtils::runProcess(self::$mergeCmd));

        copy(self::$repositoryDir . '/file.ini', '/Users/Ivan/expected-merge-conflict.ini');
        $expected = file_get_contents(__DIR__ . '/expected-merge-conflict.ini');
        $file = file_get_contents(self::$repositoryDir . '/file.ini');
        $this->assertEquals($expected, $file, 'Merge returned unexpected exit code.');

    }

    private function prepareNonConflictingData() {
        $this->prepareTestData();
    }

    private function prepareConflictingData() {
        $this->prepareTestData('Custom branch message');
    }

    private function prepareTestData($customMessage = null) {
        MergeDriverTestUtils::fillFakeFileAndCommit(self::$originDate, 'file.ini', 'Initial commit to Ancestor');

        MergeDriverTestUtils::runProcess(self::$checkoutBranchCmd);
        if ($customMessage == null) {
            MergeDriverTestUtils::fillFakeFileAndCommit(self::$branchDate, 'file.ini', 'Commit to branch');
        } else {
            MergeDriverTestUtils::fillFakeFileAndCommit(self::$branchDate, 'file.ini', 'Commit to branch', $customMessage);
        }

        MergeDriverTestUtils::runProcess(self::$checkoutMasterCmd);
        MergeDriverTestUtils::fillFakeFileAndCommit(self::$masterDate, 'file.ini', 'Commit to master');
    }


}