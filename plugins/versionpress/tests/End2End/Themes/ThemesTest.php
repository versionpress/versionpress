<?php

namespace VersionPress\Tests\End2End\Themes;

use VersionPress\Tests\End2End\Utils\End2EndTestCase;
use VersionPress\Tests\Utils\CommitAsserter;
use VersionPress\Tests\Utils\DBAsserter;

class ThemesTest extends End2EndTestCase
{

    /**
     * @see IThemesTestWorker::setThemeInfo()
     * @var array
     */
    private static $themeInfo;
    private static $secondThemeInfo;

    /** @var IThemesTestWorker */
    private static $worker;


    public static function setupBeforeClass()
    {
        parent::setUpBeforeClass();

        if (self::$testConfig->testSite->installationType !== 'standard') {
            throw new \PHPUnit_Framework_SkippedTestSuiteError();
        }

        $testDataPath = __DIR__ . '/../test-data';
        self::$themeInfo = [
            'zipfile' => realpath($testDataPath . '/test-theme.zip'),
            'stylesheet' => 'test-theme',
            'name' => 'Test Theme',
            'affected-path' => 'test-theme/*',
        ];

        self::$secondThemeInfo = [
            'zipfile' => realpath($testDataPath . '/test-theme-2.zip'),
            'stylesheet' => 'test-theme-2',
            'name' => 'Test Theme 2',
            'affected-path' => 'test-theme-2/*',
        ];

        self::$worker->setThemeInfo(self::$themeInfo);
        self::$worker->setSecondThemeInfo(self::$secondThemeInfo);
    }

    /**
     * @test
     * @testdox Uploading theme creates 'theme/install' action
     */
    public function uploadingThemeCreatesThemeInstallAction()
    {
        self::$worker->prepare_uploadTheme();

        $this->commitAsserter->reset();

        self::$worker->uploadTheme();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("theme/install");
        $this->commitAsserter->assertCommitTag("VP-Theme-Name", self::$themeInfo['name']);
        $this->commitAsserter->assertCommitPath("A", "wp-content/themes/" . self::$themeInfo['affected-path']);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Switching theme create 'theme/switch' action
     */
    public function switchingThemeCreatesThemeSwitchAction()
    {
        self::$worker->prepare_switchTheme();
        $currentTheme = self::$wpAutomation->getCurrentTheme();

        $this->commitAsserter->reset();

        self::$worker->switchTheme();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("theme/switch");
        $this->commitAsserter->assertCommitTag("VP-Theme-Name", self::$themeInfo['name']);
        $this->commitAsserter->assertCommitPath(["A", "M"], "%vpdb%/options/cu/current_theme.ini");
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();

        self::$wpAutomation->switchTheme($currentTheme);
    }

    /**
     * @test
     * @testdox Deleting theme creates 'theme/delete' action
     */
    public function deletingThemeCreatesThemeDeleteAction()
    {
        self::$worker->prepare_deleteTheme();

        $this->commitAsserter->reset();

        self::$worker->deleteTheme();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("theme/delete");
        $this->commitAsserter->assertCommitTag("VP-Theme-Name", self::$themeInfo['name']);
        $this->commitAsserter->assertCommitPath("D", "wp-content/themes/" . self::$themeInfo['affected-path']);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Uploading two themes creates a bulk action
     */
    public function uploadingTwoThemesCreatesBulkAction()
    {
        self::$worker->prepare_uploadTwoThemes();
        $this->commitAsserter->reset();

        self::$worker->uploadTwoThemes();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertBulkAction('theme/install', 2);
        $this->commitAsserter->assertCommitPath("A", "wp-content/themes/" . self::$themeInfo['affected-path']);
        $this->commitAsserter->assertCommitPath("A", "wp-content/themes/" . self::$secondThemeInfo['affected-path']);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Deleting two themes creates a bulk action
     */
    public function deletingTwoThemesCreatesBulkAction()
    {
        self::$worker->prepare_deleteTwoThemes();
        $this->commitAsserter->reset();

        self::$worker->deleteTwoThemes();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertBulkAction('theme/delete', 2);
        $this->commitAsserter->assertCommitPath("D", "wp-content/themes/" . self::$themeInfo['affected-path']);
        $this->commitAsserter->assertCommitPath("D", "wp-content/themes/" . self::$secondThemeInfo['affected-path']);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }
}
