<?php

namespace VersionPress\Tests\End2End\Posts;

use VersionPress\Tests\End2End\Utils\PostTypeTestSeleniumWorker;

class PostsTestSeleniumWorker extends PostTypeTestSeleniumWorker {

    public function getPostType() {
        return "post";
    }
}