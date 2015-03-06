<?php

namespace VersionPress\Tests\End2End\Themes;

use VersionPress\Tests\End2End\Utils\End2EndTestCase;
use VersionPress\Tests\Utils\CommitAsserter;

class ThemesTest extends End2EndTestCase {

    /**
     * @see IThemesTestWorker::setThemeInfo()
     * @var array
     */
    private static $themeInfo;

    /** @var IThemesTestWorker */
    private static $worker;


    public static function setupBeforeClass() {
        parent::setUpBeforeClass();
        $testDataPath = __DIR__ . '/../test-data';
        self::$themeInfo = array(
            'zipfile' => realpath($testDataPath . '/test-theme.zip'),
            'stylesheet' => 'test-theme',
            'name' => 'Test Theme',
            'affected-path' => 'test-theme/*',
        );

        self::$worker->setThemeInfo(self::$themeInfo);
    }

    /**
     * @test
     * @testdox Uploading theme creates 'theme/install' action
     */
    public function uploadingThemeCreatesThemeInstallAction() {
        self::$worker->prepare_uploadTheme();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->uploadTheme();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("theme/install");
        $commitAsserter->assertCommitTag("VP-Theme-Name", self::$themeInfo['name']);
        $commitAsserter->assertCommitPath("A", "wp-content/themes/" . self::$themeInfo['affected-path']);
        $commitAsserter->assertCleanWorkingDirectory();
    }

    /**
     * @test
     * @testdox Switching theme create 'theme/switch' action
     */
    public function switchingThemeCreatesThemeSwitchAction() {
        self::$worker->prepare_switchTheme();
        $currentTheme = self::$wpAutomation->getCurrentTheme();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->switchTheme();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("theme/switch");
        $commitAsserter->assertCommitTag("VP-Theme-Name", self::$themeInfo['name']);
        $commitAsserter->assertCommitPath("M", "%vpdb%/options.ini");
        $commitAsserter->assertCleanWorkingDirectory();

        self::$wpAutomation->switchTheme($currentTheme);
        file_get_contents(self::$testConfig->testSite->url . '/wp-admin/');
    }

    /**
     * @test
     * @testdox Deleting theme creates 'theme/delete' action
     */
    public function deletingThemeCreatesThemeDeleteAction() {
        self::$worker->prepare_deleteTheme();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->deleteTheme();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("theme/delete");
        $commitAsserter->assertCommitTag("VP-Theme-Name", self::$themeInfo['name']);
        $commitAsserter->assertCommitPath("D", "wp-content/themes/" . self::$themeInfo['affected-path']);
        $commitAsserter->assertCleanWorkingDirectory();
    }
}