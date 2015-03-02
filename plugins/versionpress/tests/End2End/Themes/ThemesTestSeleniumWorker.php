<?php

namespace VersionPress\Tests\End2End\Themes;

use VersionPress\Tests\End2End\Utils\SeleniumWorker;

class ThemesTestSeleniumWorker extends SeleniumWorker implements IThemesTestWorker {

    private static $themeInfo;

    public function setThemeInfo($themeInfo) {
        self::$themeInfo = $themeInfo;
    }

    public function prepare_uploadTheme() {
        $this->url('wp-admin/theme-install.php?upload');
    }

    public function uploadTheme() {
        $this->byCssSelector('input[name=themezip]')->value(self::$themeInfo['zipfile']);
        $this->byCssSelector('#install-theme-submit')->click();
        $this->waitAfterRedirect();
    }

    public function prepare_switchTheme() {
        $this->url('wp-admin/themes.php');
    }

    public function switchTheme() {
        $this->byCssSelector('div[aria-describedby*=' . self::$themeInfo['stylesheet'] . '] a.activate')->click();
        $this->waitAfterRedirect();
    }

    public function prepare_deleteTheme() {
        $this->url('wp-admin/themes.php');
    }

    public function deleteTheme() {
        $this->byCssSelector('div[aria-describedby*=' . self::$themeInfo['stylesheet'] . ']')->click();
        $this->byCssSelector('a.delete-theme')->click();
        $this->acceptAlert();
        $this->waitAfterRedirect();
    }
}