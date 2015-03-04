<?php

namespace VersionPress\Tests\End2End\Options;

use Nette\Utils\Random;
use VersionPress\Tests\End2End\Utils\WpCliWorker;

class OptionsTestWpCliWorker extends WpCliWorker implements IOptionsTestWorker
{

    private $originalBlogName;

    public function prepare_changeOption() {
        $this->originalBlogName = trim($this->wpAutomation->runWpCliCommand('option', 'get', array('blogname')));
        $this->wpAutomation->editOption('blogname', 'Some random blogname before test ' . Random::generate());
    }

    public function changeOption() {
        $this->wpAutomation->editOption('blogname', $this->originalBlogName);
    }

    public function prepare_changeMoreOptions() {
        throw new \PHPUnit_Framework_SkippedTestError("More options cannot be changed at once using WP-CLI");
    }

    public function changeMoreOptions() {
    }
}