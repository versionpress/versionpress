<?php

namespace VersionPress\Tests\End2End\Translations;

use VersionPress\Tests\End2End\Utils\WpCliWorker;

class TranslationsTestWpCliWorker extends WpCliWorker implements ITranslationsTestWorker
{

    private $originalLanguage;

    public function prepare_switchLanguage()
    {
        try {
            $this->wpAutomation->runWpCliCommand('core language', 'install', ['en_GB']);
            $this->originalLanguage = trim($this->wpAutomation->runWpCliCommand('option', 'get', ['WPLANG']));
        } catch (\Exception $e) {
            $this->originalLanguage = ''; // the language wasn't changed yet
        }
        $newLanguage = $this->originalLanguage === '' ? 'en_GB' : '';
        $this->wpAutomation->editOption('WPLANG', $newLanguage);
    }

    public function switchLanguage()
    {
        $this->wpAutomation->editOption('WPLANG', $this->originalLanguage);
    }
}
