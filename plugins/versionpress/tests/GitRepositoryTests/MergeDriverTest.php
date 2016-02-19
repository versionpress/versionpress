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
    public function isBashMergedWithoutConflictTest() {

        MergeDriverTestUtils::installMergeDriver(self::$initializationDir);
        MergeDriverTestUtils::switchDriverToBash();

        $this->prepareNonConflictingData();

        $this->assertEquals(0, MergeDriverTestUtils::runProcess(MERGE_CMD));

    }

    /**
     * @test
     */
    public function isBashMergedWithoutConflictInDateTest() {

        MergeDriverTestUtils::installMergeDriver(self::$initializationDir);
        MergeDriverTestUtils::switchDriverToBash();

        $this->prepareConflictingData();

        $this->assertEquals(1, MergeDriverTestUtils::runProcess(MERGE_CMD));

        copy(self::$repositoryDir . '/file.ini', '/Users/Ivan/expected-merge-conflict.ini');
        $expected = file_get_contents(__DIR__ . '/expected-merge-conflict.ini');
        $file = file_get_contents(self::$repositoryDir . '/file.ini');
        $this->assertEquals($expected, $file);

    }

    /**
     * @test
     */
    public function isPhpMergedWithoutConflictTest() {
        MergeDriverTestUtils::installMergeDriver(self::$initializationDir);
        MergeDriverTestUtils::switchDriverToPhp();

        $this->prepareNonConflictingData();

        $this->assertEquals(0, MergeDriverTestUtils::runProcess(MERGE_CMD));

    }

    /**
     * @test
     */
    public function isPhpMergedWithoutConflictInDateTest() {

        MergeDriverTestUtils::installMergeDriver(self::$initializationDir);
        MergeDriverTestUtils::switchDriverToPhp();

        $this->prepareConflictingData();

        $this->assertEquals(1, MergeDriverTestUtils::runProcess(MERGE_CMD));

        copy(self::$repositoryDir . '/file.ini', '/Users/Ivan/expected-merge-conflict.ini');
        $expected = file_get_contents(__DIR__ . '/expected-merge-conflict.ini');
        $file = file_get_contents(self::$repositoryDir . '/file.ini');
        $this->assertEquals($expected, $file);

    }

    private function prepareNonConflictingData() {
        $this->prepareTestData();
    }

    private function prepareConflictingData() {
        $this->prepareTestData('Custom branch message');
    }

    private function prepareTestData($customMessage = null) {
        MergeDriverTestUtils::fillFakeFileAndCommit(ORIGIN_DATE, 'file.ini', 'Initial commit to Ancestor');

        MergeDriverTestUtils::runProcess(CHECKOUT_BRANCH_CMD);
        if($customMessage==null) {
            MergeDriverTestUtils::fillFakeFileAndCommit(BRANCH_DATE, 'file.ini', 'Commit to branch');
        } else {
            MergeDriverTestUtils::fillFakeFileAndCommit(BRANCH_DATE, 'file.ini', 'Commit to branch', $customMessage);
        }

        MergeDriverTestUtils::runProcess(CHECKOUT_MASTER_CMD);
        MergeDriverTestUtils::fillFakeFileAndCommit(MASTER_DATE, 'file.ini', 'Commit to master');
    }


}
