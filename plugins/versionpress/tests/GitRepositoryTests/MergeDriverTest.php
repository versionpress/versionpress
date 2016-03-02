<?php
namespace VersionPress\Tests\GitRepositoryTests;

use VersionPress\Git\MergeDriverInstaller;
use VersionPress\Tests\Utils\MergeAsserter;
use VersionPress\Tests\Utils\MergeDriverTestUtils;
use VersionPress\Utils\IniSerializer;
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
    public function mergedDatesWithoutConflictUsingBash() {

        if (DIRECTORY_SEPARATOR == '\\') {
            $this->markTestSkipped('No Bash on Windows.');
        }

        $this->runMergeDatesWithoutConflictTest('bash');

    }

    /**
     * @test
     */
    public function mergedDatesWithoutConflictUsingPhp() {
        $this->runMergeDatesWithoutConflictTest('php');
    }

    /**
     * Creates two branches differing only in the date modified (and the GMT version of it).
     * This should result in a clean merge when our merge driver is installed.
     *
     * @param string $driver 'bash' | 'php'
     */
    private function runMergeDatesWithoutConflictTest($driver) {

        MergeDriverInstaller::installMergeDriver(self::$initializationDir);

        switch ($driver) {
            case 'bash': MergeDriverTestUtils::switchDriverToBash(); break;
            case 'php': MergeDriverTestUtils::switchDriverToPhp(); break;
        }

        MergeDriverTestUtils::writeIniFile('file.ini', '2011-11-11 11:11:11');
        MergeDriverTestUtils::commit('Initial commit to common ancestor');
        
        MergeDriverTestUtils::runGitCommand('git checkout -b test-branch');

        MergeDriverTestUtils::writeIniFile('file.ini', '2012-12-12 12:12:12');
        MergeDriverTestUtils::commit('Commit to branch');

        MergeDriverTestUtils::runGitCommand('git checkout master');

        MergeDriverTestUtils::writeIniFile('file.ini', '2013-03-03 13:13:13');
        MergeDriverTestUtils::commit('Commit to master');

        MergeAsserter::assertCleanMerge('git merge test-branch');

    }


    /**
     * @test
     */
    public function conflictingContentsEndsWithGitConflictUsingBash() {

        if (DIRECTORY_SEPARATOR == '\\') {
            $this->markTestSkipped('No Bash on Windows.');
        }

        $this->runMergeWithExpectedConflictTest('bash');

    }

    /**
     * @test
     */
    public function conflictingContentsEndsWithGitConflictUsingPhp() {
        $this->runMergeWithExpectedConflictTest('php');
    }


    /**
     * Creates two branches with a conflict in `content`. Asserts that
     * dates are merged automatically but the content conflicts.
     *
     * @param string $driver 'bash' | 'php'
     */
    private function runMergeWithExpectedConflictTest($driver) {

        MergeDriverInstaller::installMergeDriver(self::$initializationDir);

        switch ($driver) {
            case 'bash': MergeDriverTestUtils::switchDriverToBash(); break;
            case 'php': MergeDriverTestUtils::switchDriverToPhp(); break;
        }

        MergeDriverTestUtils::writeIniFile('file.ini', '2011-11-11 11:11:11');
        MergeDriverTestUtils::commit('Initial commit to common ancestor');

        MergeDriverTestUtils::runGitCommand('git checkout -b test-branch');

        MergeDriverTestUtils::writeIniFile('file.ini', '2012-12-12 12:12:12', 'Modified in branch');
        MergeDriverTestUtils::commit('Commit to branch');

        MergeDriverTestUtils::runGitCommand('git checkout master');

        MergeDriverTestUtils::writeIniFile('file.ini', '2013-03-03 13:13:13', 'Modified in master');
        MergeDriverTestUtils::commit('Commit to master');

        MergeAsserter::assertMergeConflict('git merge test-branch');

        $expected = StringUtils::crlfize(file_get_contents(__DIR__ . '/expected-merge-conflict.ini'));
        $actual = StringUtils::crlfize(file_get_contents(self::$repositoryDir . '/file.ini'));
        $this->assertEquals($expected, $actual);

    }



    /**
     * @test
     */
    public function changesOnAdjacentLinesMergeWithoutConflictBash() {

        if (DIRECTORY_SEPARATOR == '\\') {
            $this->markTestSkipped('No Bash on Windows.');
        }

        $this->runAdjacentLineMergeTest('bash');

    }


    /**
     * @test
     */
    public function changesOnAdjacentLinesMergeWithoutConflictUsingPhp() {
        $this->runAdjacentLineMergeTest('php');
    }

    /**
     *
     * @param string $driver 'bash' | 'php'
     */

    private function runAdjacentLineMergeTest($driver) {
        MergeDriverInstaller::installMergeDriver(self::$initializationDir);

        switch ($driver) {
            case 'bash': MergeDriverTestUtils::switchDriverToBash(); break;
            case 'php': MergeDriverTestUtils::switchDriverToPhp(); break;
        }

        $date = '2011-11-11 11:11:11';

        MergeDriverTestUtils::writeIniFile('file.ini', $date, 'Default content', 'Default title');
        MergeDriverTestUtils::commit('Initial commit to common ancestor');

        MergeDriverTestUtils::runGitCommand('git checkout -b test-branch');

        MergeDriverTestUtils::writeIniFile('file.ini', $date, 'Default content', 'CHANGED TITLE');
        MergeDriverTestUtils::commit('Commit to branch');

        MergeDriverTestUtils::runGitCommand('git checkout master');

        MergeDriverTestUtils::writeIniFile('file.ini', $date, 'CHANGED CONTENT', 'Default title');
        MergeDriverTestUtils::commit('Commit to master');

        MergeAsserter::assertCleanMerge('git merge test-branch');
    }



}
