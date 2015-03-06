<?php

namespace VersionPress\Tests\End2End\Pages;

use VersionPress\Tests\End2End\Utils\PostTypeTestWpCliWorker;

class PagesTestWpCliWorker extends PostTypeTestWpCliWorker {

    public function getPostType() {
        return "page";
    }
}