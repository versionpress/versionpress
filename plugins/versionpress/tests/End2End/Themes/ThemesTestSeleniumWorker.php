<?php

namespace VersionPress\Tests\End2End\Themes;

use VersionPress\Tests\End2End\Utils\SeleniumWorker;

class ThemesTestSeleniumWorker extends SeleniumWorker implements IThemesTestWorker
{

    private static $themeInfo;

    public function setThemeInfo($themeInfo)
    {
        self::$themeInfo = $themeInfo;
    }

    public function setSecondThemeInfo($themeInfo)
    {
        // we don't need second theme - selenium worker does not support bulk operations
    }

    public function prepare_uploadTheme()
    {
        $this->url(self::$wpAdminPath . '/theme-install.php?upload');
    }

    public function uploadTheme()
    {
        $this->byCssSelector('input[name=themezip]')->value(self::$themeInfo['zipfile']);
        $this->byCssSelector('#install-theme-submit')->click();
        $this->waitAfterRedirect();
    }

    public function prepare_switchTheme()
    {
        $this->url(self::$wpAdminPath . '/themes.php');
    }

    public function switchTheme()
    {
        $this->byCssSelector('div[aria-describedby*=' . self::$themeInfo['stylesheet'] . '] a.activate')->click();
        $this->waitAfterRedirect();
    }

    public function prepare_deleteTheme()
    {
        $this->url(self::$wpAdminPath . '/themes.php');
    }

    public function deleteTheme()
    {
        $this->byCssSelector('div[aria-describedby*=' . self::$themeInfo['stylesheet'] . ']')->click();
        $this->byCssSelector('a.delete-theme')->click();
        $this->acceptAlert();
        $this->waitAfterRedirect();
    }

    public function prepare_uploadTwoThemes()
    {
        throw new \PHPUnit_Framework_SkippedTestError('There is no way to upload more themes at once using selenium');
    }

    public function uploadTwoThemes()
    {
    }

    public function prepare_deleteTwoThemes()
    {
        throw new \PHPUnit_Framework_SkippedTestError('There is no way to delete more themes at once using selenium');
    }

    public function deleteTwoThemes()
    {
    }
}
