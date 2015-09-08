<?php

namespace VersionPress\Tests\End2End\Translations;

use Nette\Utils\Random;
use VersionPress\Tests\End2End\Utils\WpCliWorker;

class TranslationsTestWpCliWorker extends WpCliWorker implements ITranslationsTestWorker
{

    private $originalLanguage;

    public function prepare_switchLanguage() {
        $this->originalLanguage = trim($this->wpAutomation->runWpCliCommand('option', 'get', array('WPLANG')));
        $newLanguage = $this->originalLanguage === '' ? 'en_GB' : '';
        $this->wpAutomation->editOption('WPLANG', $newLanguage);
    }

    public function switchLanguage() {
        $this->wpAutomation->editOption('WPLANG', $this->originalLanguage);
    }
}
