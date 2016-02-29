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
    public function mergeDriverInstalledCorrectly() {
        MergeDriverInstaller::installMergeDriver(self::$initializationDir);
        $this->assertContains('vp-ini', file_get_contents(self::$repositoryDir . "/.git/config"));
        $this->assertContains('merge=vp-ini', file_get_contents(self::$repositoryDir . "/.gitattributes"));
    }

    /**
     * @test
     */
    public function mergeDriverUninstalledCorrectly() {
        MergeDriverInstaller::installMergeDriver(self::$initializationDir);
        MergeDriverInstaller::uninstallMergeDriver();
        $this->assertNotContains('vp-ini', file_get_contents(self::$repositoryDir . "/.git/config"));
        $this->assertNotContains('merge=vp-ini', file_get_contents(self::$repositoryDir . "/.gitattributes"));
    }

    /**
     * @test
     */
    public function mergedWithoutConflictUsingBash() {

        if (DIRECTORY_SEPARATOR == '\\') {
            $this->markTestSkipped('mergedWithoutConflictUsingBash is skipped (no Bash on Windows).');
        }

        MergeDriverInstaller::installMergeDriver(self::$initializationDir);
        MergeDriverTestUtils::switchDriverToBash();

        $this->prepareNonConflictingData();

        $this->assertEquals(0, MergeDriverTestUtils::getProcessExitCode('git merge test-branch'), 'Merge returned unexpected exit code.');

    }

    /**
     * @test
     */
    public function mergedWithExpectedConflictUsingBash() {

        if (DIRECTORY_SEPARATOR == '\\') {
            $this->markTestSkipped('mergedWithoutConflictInDateUsingBash is skipped (no Bash on Windows).');
        }

        MergeDriverInstaller::installMergeDriver(self::$initializationDir);
        MergeDriverTestUtils::switchDriverToBash();

        $this->prepareConflictingData();

        $this->assertEquals(1, MergeDriverTestUtils::getProcessExitCode('git merge test-branch'), 'Merge returned unexpected exit code.');

        $expected = StringUtils::crlfize(file_get_contents(__DIR__ . '/expected-merge-conflict.ini'));
        $file = StringUtils::crlfize(file_get_contents(self::$repositoryDir . '/file.ini'));
        $this->assertEquals($expected, $file);

    }

    /**
     * @test
     */
    public function mergedWithoutConflictUsingPhp() {
        MergeDriverInstaller::installMergeDriver(self::$initializationDir);
        MergeDriverTestUtils::switchDriverToPhp();

        $this->prepareNonConflictingData();

        $this->assertEquals(0, MergeDriverTestUtils::getProcessExitCode('git merge test-branch'), 'Merge returned unexpected exit code.');

    }

    /**
     * @test
     */
    public function changesOnAdjacentLinesMergeWithoutConflictUsingPhp() {
        MergeDriverInstaller::installMergeDriver(self::$initializationDir);
        MergeDriverTestUtils::switchDriverToPhp();

        $this->prepareDataWithChangedAdjacentLines();

        $this->assertEquals(0, MergeDriverTestUtils::getProcessExitCode('git merge test-branch'), 'Merge returned unexpected exit code.');

    }

    /**
     * @test
     */
    public function changesOnAdjacentLinesMergeWithoutConflictBash() {
        if (DIRECTORY_SEPARATOR == '\\') {
            $this->markTestSkipped('changesOnAdjacentLinesMergeWithoutConflictUsingBash is skipped (no Bash on Windows).');
        }
        MergeDriverInstaller::installMergeDriver(self::$initializationDir);
        MergeDriverTestUtils::switchDriverToBash();

        $this->prepareDataWithChangedAdjacentLines();

        $this->assertEquals(0, MergeDriverTestUtils::getProcessExitCode('git merge test-branch'), 'Merge returned unexpected exit code.');

    }

    /**
     * @test
     */
    public function mergedWithExpectedConflictUsingPhp() {

        MergeDriverInstaller::installMergeDriver(self::$initializationDir);
        MergeDriverTestUtils::switchDriverToPhp();

        $this->prepareConflictingData();

        $this->assertEquals(1, MergeDriverTestUtils::getProcessExitCode('git merge test-branch'), 'Merge returned unexpected exit code.');

        $expected = StringUtils::crlfize(file_get_contents(__DIR__ . '/expected-merge-conflict.ini'));
        $file = StringUtils::crlfize(file_get_contents(self::$repositoryDir . '/file.ini'));
        $this->assertEquals($expected, $file);

    }

    /**
     * @test
     */
    public function mergedFileWithoutDateFieldsUsingPhp() {
        MergeDriverInstaller::installMergeDriver(self::$initializationDir);
        MergeDriverTestUtils::switchDriverToPhp();

        $this->prepareRepositoryhistoryForTestingMergeWithoutDateFields();

        $this->assertEquals(0, MergeDriverTestUtils::getProcessExitCode('git merge test-branch'), 'Merge returned unexpected exit code.');
    }

    /**
     * @test
     */
    public function mergedFileWithoutDateFieldsUsingBash() {
        if (DIRECTORY_SEPARATOR == '\\') {
            $this->markTestSkipped('mergedFileWithoutDateFieldsUsingBash is skipped (no Bash on Windows).');
        }
        MergeDriverInstaller::installMergeDriver(self::$initializationDir);
        MergeDriverTestUtils::switchDriverToBash();

        $this->prepareRepositoryhistoryForTestingMergeWithoutDateFields();

        $this->assertEquals(0, MergeDriverTestUtils::getProcessExitCode('git merge test-branch'), 'Merge returned unexpected exit code.');
    }


    private function prepareNonConflictingData() {
        $this->prepareRepositoryHistoryForTestingDateFieldMerge();
    }

    private function prepareDataWithChangedAdjacentLines() {
        $this->prepareRepositoryHistoryForTestingDateFieldMerge(false, true);
    }

    private function prepareConflictingData() {
        $this->prepareRepositoryHistoryForTestingDateFieldMerge(true, false);
    }

    private function prepareRepositoryHistoryForTestingDateFieldMerge($createConflict = false, $changeTitle = false) {

        $originDate = '10-02-16 08:00:00';
        $masterDate = '15-02-16 12:00:11';
        $branchDate = '17-02-16 19:19:23';

        MergeDriverTestUtils::createIniFileAndCommit($originDate, 'file.ini', 'Initial commit to Ancestor');
        MergeDriverTestUtils::getProcessExitCode('git checkout -b test-branch');

        if ($createConflict == false) {
            MergeDriverTestUtils::createIniFileAndCommit($branchDate, 'file.ini', 'Commit to branch');
        } else {
            MergeDriverTestUtils::createIniFileAndCommit($branchDate, 'file.ini', 'Commit to branch', 'Custom branch message');
        }

        MergeDriverTestUtils::getProcessExitCode('git checkout master');
        if ($changeTitle == false) {
            MergeDriverTestUtils::createIniFileAndCommit($masterDate, 'file.ini', 'Commit to master', 'Custom content in master');
        } else {
            MergeDriverTestUtils::createIniFileAndCommit($masterDate, 'file.ini', 'Commit to master', 'Custom content in master', 'Custom title in master');
        }
    }

    private function prepareRepositoryhistoryForTestingMergeWithoutDateFields() {
        MergeDriverTestUtils::createIniFileWithoutDateFieldsAndCommit('file.ini', 'Initial commit to Ancestor');
        MergeDriverTestUtils::getProcessExitCode('git checkout -b test-branch');
        MergeDriverTestUtils::createIniFileWithoutDateFieldsAndCommit('file.ini', 'Commit to branch');
        MergeDriverTestUtils::getProcessExitCode('git checkout master');
        MergeDriverTestUtils::createIniFileWithoutDateFieldsAndCommit('file.ini', 'Commit to master', 'Custom content in master', 'Custom title in master');
    }


}
