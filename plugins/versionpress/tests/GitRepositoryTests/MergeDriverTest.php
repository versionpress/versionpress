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

    /**
     * @var GitRepository
     */
    private static $gitRepository;

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
        self::destroyRepository();
    }

    static function initRepository() {
        $driverScriptName = 'ini-merge.php';
        $driverScript = '../../src/Git/MergeDrivers/' . $driverScriptName;
        $driverScriptFakeDir = self::$repositoryDir . '/src/Git/MergeDrivers';
        FileSystem::remove(self::$repositoryDir);
        mkdir(self::$repositoryDir);
        FileSystem::mkdir($driverScriptFakeDir);
        self::$gitRepository = new GitRepository(self::$repositoryDir, __DIR__);
        self::$gitRepository->init();
        copy($driverScript, $driverScriptFakeDir . '/' . $driverScriptName);
    }


    static function destroyRepository() {
        FileSystem::remove(self::$repositoryDir);
    }

    /**
     * @test
     */
    public function isGitAttributesSetTest() {
            MergeDriverInstaller::installGitattributes(self::$initializationDir);
            $this->assertContains('merge=vp-ini', file_get_contents(self::$repositoryDir . "/.gitattributes"));
    }

    /**
     * @test
     */
    public function isMergeDriverInstalledTest() {
            MergeDriverInstaller::installGitMergeDriver(self::$initializationDir);
            $this->assertContains('vp-ini', file_get_contents(self::$repositoryDir . "/.git/config"));
    }

    /**
     * @test
     */
    public function isMergedWithoutConflictTest() {

            MergeDriverTestUtils::installMergeDriver(self::$initializationDir);

            MergeDriverTestUtils::fillFakeFileAndCommit(ORIGIN_DATE, 'Initial commit to Ancestor');

            MergeDriverTestUtils::runProcess(CHECKOUT_BRANCH_CMD);
            MergeDriverTestUtils::fillFakeFileAndCommit(BRANCH_DATE, 'Commit to branch');

            MergeDriverTestUtils::runProcess(CHECKOUT_MASTER_CMD);
            MergeDriverTestUtils::fillFakeFileAndCommit(MASTER_DATE, 'Commit to master');

            $this->assertEquals(0, MergeDriverTestUtils::runProcess(MERGE_CMD));

    }

    /**
     * @test
     */
    public function isMergedWithoutConflictInDateTest() {

            MergeDriverTestUtils::installMergeDriver(self::$initializationDir);

            MergeDriverTestUtils::fillFakeFileAndCommit(ORIGIN_DATE, 'Initial commit to Ancestor');

            MergeDriverTestUtils::runProcess(CHECKOUT_BRANCH_CMD);
            MergeDriverTestUtils::fillFakeFileAndCommit(BRANCH_DATE, 'Commit to branch', 'Custom branch message');

            MergeDriverTestUtils::runProcess(CHECKOUT_MASTER_CMD);
            MergeDriverTestUtils::fillFakeFileAndCommit(MASTER_DATE, 'Commit to master');

            $this->assertEquals(1, MergeDriverTestUtils::runProcess(MERGE_CMD));
            $expected = file_get_contents(__DIR__ . '/expected-merge-conflict.ini');
            $file = file_get_contents(self::$repositoryDir . '/file.ini');
            $this->assertEquals($expected, $file);

    }


}
