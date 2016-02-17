<?php
namespace VersionPress\Tests\GitRepositoryTests;

use DateTime;
use VersionPress\Git\GitConfig;
use VersionPress\Git\GitRepository;
use VersionPress\Git\MergeDriverInstaller;
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


    public static function tearDownAfterClass() {
        self::destroyRepository();
    }

    static function initRepository($installMergeDriver = false) {
        $driverScriptName = 'ini-merge.php';
        $driverScript = '../../src/Git/MergeDrivers/' . $driverScriptName;
        $driverScriptFakeDir = self::$repositoryDir . '/src/Git/MergeDrivers';
        FileSystem::remove(self::$repositoryDir);
        mkdir(self::$repositoryDir);
        FileSystem::mkdir($driverScriptFakeDir);
        self::$gitRepository = new GitRepository(self::$repositoryDir, __DIR__);
        self::$gitRepository->init();
        copy($driverScript, $driverScriptFakeDir . '/' . $driverScriptName);
        if ($installMergeDriver) {
            MergeDriverInstaller::installGitattributes(self::$initializationDir);
            MergeDriverInstaller::installGitMergeDriver(self::$initializationDir);
        }
    }


    private static function doInRepository($callback, $installMergeDriver = false) {
        self::initRepository($installMergeDriver);
        $callback();
        self::destroyRepository();
    }


    static function destroyRepository() {
        FileSystem::remove(self::$repositoryDir);
    }

    /**
     * @test
     */
    public function isGitAttributesSetTest() {
        self::doInRepository(function () {
            MergeDriverInstaller::installGitattributes(self::$initializationDir);
            $this->assertContains('merge=vp-ini', file_get_contents(self::$repositoryDir . "/.gitattributes"));
        });

    }

    /**
     * @test
     */
    public function isMergeDriverInstalledTest() {
        self::doInRepository(function () {
            MergeDriverInstaller::installGitMergeDriver(self::$initializationDir);
            $this->assertContains('vp-ini', file_get_contents(self::$repositoryDir . "/.git/config"));
        });
    }

    /**
     * @test
     */
    public function isMergedWithoutConflictTest() {

        self::doInRepository(function () {
            $this->fillFakeFileAndCommit(ORIGIN_DATE, 'Initial commit to Ancestor');

            $this->runProcess(CHECKOUT_BRANCH_CMD);
            $this->fillFakeFileAndCommit(BRANCH_DATE, 'Commit to branch');

            $this->runProcess(CHECKOUT_MASTER_CMD);
            $this->fillFakeFileAndCommit(MASTER_DATE, 'Commit to master');

            $this->assertEquals(0, $this->runProcess(MERGE_CMD));
        }, true);
    }

    /**
     * @test
     */
    public function isMergedWithoutConflictInDateTest() {

        self::doInRepository(function () {

            $this->fillFakeFileAndCommit(ORIGIN_DATE, 'Initial commit to Ancestor');

            $this->runProcess(CHECKOUT_BRANCH_CMD);
            $this->fillFakeFileAndCommit(BRANCH_DATE, 'Commit to branch', 'Custom branch message');

            $this->runProcess(CHECKOUT_MASTER_CMD);
            $this->fillFakeFileAndCommit(MASTER_DATE, 'Commit to master');

            $this->assertEquals(1, $this->runProcess(MERGE_CMD));
            $expected = file_get_contents(__DIR__ . '/expected-merge-conflict.ini');
            $file = file_get_contents(self::$repositoryDir . '/file.ini');
            $this->assertEquals($expected, $file);

        }, true);
    }


    private function fillFakeFileAndCommit($originDate, $message = 'Fake commit message', $content = 'Fake content') {
        $originData = array("GUID" => array('post_modified' => $originDate, 'post_modified_gmt' => $originDate, 'content' => $content));
        file_put_contents(self::$repositoryDir . '/file.ini', IniSerializer::serialize($originData));
        self::$gitRepository->stageAll();
        self::$gitRepository->commit($message, GitConfig::$wpcliUserName, GitConfig::$wpcliUserEmail);
    }

    /**
     * @param $checkoutBranchCmd
     * @return Process
     */
    private function runProcess($checkoutBranchCmd) {
        $process = new Process($checkoutBranchCmd, self::$repositoryDir);
        $process->run();
        return $process->getExitCode();
    }


}
