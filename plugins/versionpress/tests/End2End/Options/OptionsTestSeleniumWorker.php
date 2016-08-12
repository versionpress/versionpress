<?php

namespace VersionPress\Tests\End2End\Options;

use VersionPress\Tests\End2End\Utils\SeleniumWorker;

class OptionsTestSeleniumWorker extends SeleniumWorker implements IOptionsTestWorker
{

    private $originalBlogname;

    public function prepare_changeOption()
    {
        $this->url(self::$wpAdminPath . '/options-general.php');
        $this->originalBlogname = $this->byCssSelector('#blogname')->value();
        $this->byCssSelector('#blogname')->value(' edit');
        $this->byCssSelector('#submit')->click();
        $this->waitAfterRedirect();
    }

    public function changeOption()
    {
        $this->byCssSelector('#blogname')->clear();
        $this->byCssSelector('#blogname')->value($this->originalBlogname);
        $this->byCssSelector('#submit')->click();
        $this->waitAfterRedirect();
    }

    public function prepare_changeTwoOptions()
    {
        $this->url(self::$wpAdminPath . '/options-general.php');
    }

    public function changeTwoOptions()
    {
        $this->byCssSelector('#blogname')->value(' edit');
        $this->byCssSelector('#blogdescription')->value(' edit');

        $this->byCssSelector('#submit')->click();
        $this->waitAfterRedirect();
    }
}
