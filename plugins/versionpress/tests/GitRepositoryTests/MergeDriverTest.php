<?php
namespace VersionPress\Tests\GitRepositoryTests;

use VersionPress\Git\MergeDriverInstaller;
use VersionPress\Tests\Utils\MergeAsserter;
use VersionPress\Tests\Utils\MergeDriverTestUtils;
use VersionPress\Utils\StringUtils;

class MergeDriverTest extends \PHPUnit_Framework_TestCase
{

    private static $repositoryDir;

    public function driverProvider()
    {
        return [
            [MergeDriverInstaller::DRIVER_BASH],
            [MergeDriverInstaller::DRIVER_PHP],
            [MergeDriverInstaller::DRIVER_GO],
        ];
    }

    public static function setUpBeforeClass()
    {
        self::$repositoryDir = sys_get_temp_dir() . '/vp-repository';
        mkdir(self::$repositoryDir);
    }

    public function setUp()
    {
        MergeDriverTestUtils::initRepository(self::$repositoryDir);
    }

    public function tearDown()
    {
        MergeDriverTestUtils::destroyRepository();
    }

    /**
     * @param string $driver See MergeDriverInstaller::installMergeDriver()'s $driver parameter
     */
    private function installMergeDriver($driver)
    {
        MergeDriverInstaller::installMergeDriver(
            self::$repositoryDir,
            __DIR__ . '/../..',
            self::$repositoryDir,
            $driver
        );
    }


    /**
     * @test
     */
    public function mergeDriverInstalledCorrectly()
    {
        $this->installMergeDriver('auto');
        $this->assertContains('vp-ini', file_get_contents(self::$repositoryDir . "/.git/config"));
        $this->assertContains('merge=vp-ini', file_get_contents(self::$repositoryDir . "/.gitattributes"));
    }

    /**
     * @test
     */
    public function mergeDriverUninstalledCorrectly()
    {
        $this->installMergeDriver('auto');
        file_put_contents(self::$repositoryDir . "/.gitattributes", "*.txt text");
        MergeDriverInstaller::uninstallMergeDriver(self::$repositoryDir, __DIR__ . '/../..', self::$repositoryDir);
        $this->assertNotContains('vp-ini', file_get_contents(self::$repositoryDir . "/.git/config"));
        $this->assertNotContains('merge=vp-ini', file_get_contents(self::$repositoryDir . "/.gitattributes"));
    }

    /**
     * @test
     */
    public function gitAttributesRemovedWhenEmpty()
    {
        $this->installMergeDriver('auto');
        MergeDriverInstaller::uninstallMergeDriver(self::$repositoryDir, __DIR__ . '/../..', self::$repositoryDir);
        $this->assertFileNotExists(self::$repositoryDir . "/.gitattributes");
    }

    /**
     * @test
     */
    public function gitAttributesIsSameAfterInstallAndUninstall()
    {
        $gitAttributesContent = "*.txt text\n*.jpg binary";
        file_put_contents(self::$repositoryDir . "/.gitattributes", $gitAttributesContent);
        $this->installMergeDriver('auto');
        MergeDriverInstaller::uninstallMergeDriver(self::$repositoryDir, __DIR__ . '/../..', self::$repositoryDir);
        $this->assertEquals($gitAttributesContent, file_get_contents(self::$repositoryDir . "/.gitattributes"));
    }

    /**
     * @test
     */
    public function mergeDriverIsNotAddedWhenPresent()
    {
        $gitattributesContents = file_get_contents(__DIR__ . '/../../src/Initialization/.gitattributes.tpl');
        $gitConfigContents = "[merge \"vp-ini\"]";
        $gitattributesVariables = [
            'vpdb-dir' => self::$repositoryDir
        ];
        $gitattributesContents = StringUtils::fillTemplateString($gitattributesVariables, $gitattributesContents);
        file_put_contents(self::$repositoryDir . "/.gitattributes", $gitattributesContents);
        file_put_contents(self::$repositoryDir . "/.git/config", $gitConfigContents);
        $this->installMergeDriver('auto');
        $this->assertEquals($gitConfigContents, file_get_contents(self::$repositoryDir . "/.git/config"));
        $this->assertEquals($gitattributesContents, file_get_contents(self::$repositoryDir . "/.gitattributes"));
    }

    /**
     * @test
     */
    public function gitAttributesRemovedCorrectlyAfterUninstall()
    {
        $this->installMergeDriver('auto');
        MergeDriverInstaller::uninstallMergeDriver(self::$repositoryDir, __DIR__ . '/../..', self::$repositoryDir);
        $this->assertNotContains('vp-ini', file_get_contents(self::$repositoryDir . "/.git/config"));
        $this->assertFileNotExists(self::$repositoryDir . "/.gitattributes");
    }

    /**
     * Creates two branches differing only in the date modified (and the GMT version of it).
     * This should result in a clean merge when our merge driver is installed.
     *
     * @test
     * @dataProvider driverProvider
     * @param string $driver
     */
    public function mergedDatesWithoutConflict($driver)
    {

        if (DIRECTORY_SEPARATOR == '\\' && $driver == MergeDriverInstaller::DRIVER_BASH) {
            $this->markTestSkipped('No Bash on Windows.');
            return;
        }

        $this->installMergeDriver($driver);

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
     * Creates two branches with a conflict in `content`. Asserts that
     * dates are merged automatically but the content conflicts.
     *
     * @test
     * @dataProvider driverProvider
     * @param string $driver
     */
    public function conflictingContentsCreatedConflict($driver)
    {

        if (DIRECTORY_SEPARATOR == '\\' && $driver == MergeDriverInstaller::DRIVER_BASH) {
            $this->markTestSkipped('No Bash on Windows.');
            return;
        }

        $this->installMergeDriver($driver);

        MergeDriverTestUtils::writeIniFile('file.ini', '2011-11-11 11:11:11');
        MergeDriverTestUtils::commit('Initial commit to common ancestor');

        MergeDriverTestUtils::runGitCommand('git checkout -b test-branch');

        MergeDriverTestUtils::writeIniFile('file.ini', '2012-12-12 12:12:12', 'Modified in branch');
        MergeDriverTestUtils::commit('Commit to branch');

        MergeDriverTestUtils::runGitCommand('git checkout master');

        MergeDriverTestUtils::writeIniFile('file.ini', '2013-03-03 13:13:13', 'Modified in master');
        MergeDriverTestUtils::commit('Commit to master');

        MergeAsserter::assertMergeConflict('git merge test-branch');

        $expected = StringUtils::ensureLf(file_get_contents(__DIR__ . '/expected-merge-conflict.ini'));
        $actual = StringUtils::ensureLf(file_get_contents(self::$repositoryDir . '/file.ini'));
        $this->assertEquals($expected, $actual);
    }


    /**
     *
     * @test
     * @dataProvider driverProvider
     * @param string $driver
     */
    public function changesOnAdjacentLinesMergeWithoutConflict($driver)
    {

        if (DIRECTORY_SEPARATOR == '\\' && $driver == MergeDriverInstaller::DRIVER_BASH) {
            $this->markTestSkipped('No Bash on Windows.');
            return;
        }

        $this->installMergeDriver($driver);

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
