<?php

namespace VersionPress\Tests\End2End\Posts;

use Nette\Utils\Random;
use VersionPress\Tests\End2End\Utils\PostTypeTestWpCliWorker;

class PostsTestWpCliWorker extends PostTypeTestWpCliWorker {

    private $postId;

    public function getPostType() {
        return "post";
    }

    public function prepare_createTagInEditationForm() {
        $this->postId = $this->wpAutomation->createPost($this->testPost);
    }

    public function createTagInEditationForm() {
        $this->wpAutomation->runWpCliCommand('post', 'term', array('add', $this->postId, 'post_tag', Random::generate()));
    }

    public function prepare_changePostFormat() {
        throw new \PHPUnit_Framework_SkippedTestError("Post format cannot be changed using WP-CLI.");
    }

    public function changePostFormat() {

    }
}