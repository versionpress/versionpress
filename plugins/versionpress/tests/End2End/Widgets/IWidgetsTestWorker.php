<?php

namespace VersionPress\Tests\End2End\Widgets;

use VersionPress\Tests\End2End\Utils\ITestWorker;

interface IWidgetsTestWorker extends ITestWorker
{

    public function prepare_createWidget();

    public function createWidget();

    public function prepare_editWidget();

    public function editWidget();

    public function prepare_deleteWidget();

    public function deleteWidget();
}
