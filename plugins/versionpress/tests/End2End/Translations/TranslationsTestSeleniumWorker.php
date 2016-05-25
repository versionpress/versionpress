<?php

namespace VersionPress\Tests\End2End\Translations;

use VersionPress\Tests\End2End\Utils\SeleniumWorker;

class TranslationsTestSeleniumWorker extends SeleniumWorker implements ITranslationsTestWorker
{

    public function prepare_switchLanguage()
    {
        $this->url(self::$wpAdminPath . '/options-general.php');
    }

    public function switchLanguage()
    {
        $newValue = $this->byCssSelector('#WPLANG')->value() === '' ? 'en_GB' : '';
        $this->select($this->byCssSelector('#WPLANG'))->selectOptionByValue($newValue);
        $this->byCssSelector('#submit')->click();
        $this->waitAfterRedirect();
    }
}
