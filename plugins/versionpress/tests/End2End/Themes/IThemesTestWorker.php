<?php

namespace VersionPress\Tests\End2End\Themes;

use VersionPress\Tests\End2End\Utils\ITestWorker;

interface IThemesTestWorker extends ITestWorker {

    /**
     * @param array $themeInfo Required keys are:
     *                           zipfile: absolute path used for upload
     *                           stylesheet: id of theme (usually sanitized name)
     *                           name: name of plugin (saved in VP-Theme-Name tag)
     *                           affected-path: directory or file that should be affected by installation / deleting
     *
     * @return mixed
     */
    public function setThemeInfo($themeInfo);

    public function prepare_uploadTheme();
    public function uploadTheme();

    public function prepare_switchTheme();
    public function switchTheme();

    public function prepare_deleteTheme();
    public function deleteTheme();
}