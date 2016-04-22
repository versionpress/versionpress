<?php

namespace VersionPress\Tests\End2End\Translations;

use VersionPress\Tests\End2End\Utils\ITestWorker;

interface ITranslationsTestWorker extends ITestWorker
{

    public function prepare_switchLanguage();

    public function switchLanguage();
}
