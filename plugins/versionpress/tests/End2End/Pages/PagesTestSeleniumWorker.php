<?php

namespace VersionPress\Tests\End2End\Pages;

use VersionPress\Tests\End2End\Utils\PostTypeTestSeleniumWorker;

class PagesTestSeleniumWorker extends PostTypeTestSeleniumWorker {

    public function getPostType() {
        return "page";
    }
}