<?php

class ThemeTest extends SeleniumTestCase {

    /**
     * Required keys are:
     * zipfile: absolute path used for upload
     * stylesheet: id of theme (usually sanitized name)
     * name: name of plugin (saved in VP-Theme-Name tag)
     * affected-path: directory or file that should be affected by installation / deleting
     *
     * @var array
     */
    private static $themeInfo;


    public static function setupBeforeClass() {
        parent::setUpBeforeClass();
        $testDataPath = __DIR__ . DIRECTORY_SEPARATOR . 'test-data' . DIRECTORY_SEPARATOR;
        self::$themeInfo = array(
            'zipfile' => $testDataPath . 'test-theme.zip',
            'stylesheet' => 'test-theme',
            'name' => 'Test Theme',
            'affected-path' => 'test-theme/*',
        );
    }

    /**
     * @test
     * @testdox Uploading theme creates 'theme/install' action
     */
    public function uploadingThemeCreatesThemeInstallAction() {
        $this->url('wp-admin/theme-install.php?upload');

        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->byCssSelector('input[name=themezip]')->value(self::$themeInfo['zipfile']);
        $this->byCssSelector('#install-theme-submit')->click();
        $this->waitAfterRedirect();

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
        $this->url('wp-admin/themes.php');
        $currentTheme = self::$wpAutomation->getCurrentTheme();

        $commitAsserter = new CommitAsserter($this->gitRepository);
        $this->byCssSelector('div[aria-describedby*=' . self::$themeInfo['stylesheet'] . '] a.activate')->click();
        $this->waitAfterRedirect();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("theme/switch");
        $commitAsserter->assertCommitTag("VP-Theme-Name", self::$themeInfo['name']);
        $commitAsserter->assertCommitPath("M", "%vpdb%/options.ini");
        $commitAsserter->assertCleanWorkingDirectory();

        self::$wpAutomation->switchTheme($currentTheme);
    }

    /**
     * @test
     * @testdox Deleting theme creates 'theme/delete' action
     */
    public function deletingThemeCreatesThemeDeleteAction() {
        $this->url('wp-admin/themes.php');

        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->byCssSelector('div[aria-describedby*=' . self::$themeInfo['stylesheet'] . ']')->click();
        $this->byCssSelector('a.delete-theme')->click();
        $this->acceptAlert();
        $this->waitAfterRedirect();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("theme/delete");
        $commitAsserter->assertCommitTag("VP-Theme-Name", self::$themeInfo['name']);
        $commitAsserter->assertCommitPath("D", "wp-content/themes/" . self::$themeInfo['affected-path']);
        $commitAsserter->assertCleanWorkingDirectory();
    }
}
