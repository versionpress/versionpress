<?php

namespace VersionPress\Tests\End2End\Posts;

use VersionPress\Tests\End2End\Utils\PostTypeTestWpCliWorker;

class PostsTestWpCliWorker extends PostTypeTestWpCliWorker {

    public function getPostType() {
        return "post";
    }
}