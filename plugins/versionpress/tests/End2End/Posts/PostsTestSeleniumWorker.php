<?php

namespace VersionPress\Tests\End2End\Posts;

use VersionPress\Tests\End2End\Utils\PostTypeTestSeleniumWorker;

class PostsTestSeleniumWorker extends PostTypeTestSeleniumWorker {

    public function getPostType() {
        return "post";
    }

    public function prepare_createTagInEditationForm() {
        $this->url($this->getPostTypeScreenUrl());
        $this->jsClickAndWait('#the-list tr:first-child .row-actions .edit a');
        $this->waitAfterRedirect();
    }

    public function createTagInEditationForm() {
        $this->byCssSelector('#new-tag-post_tag')->value('some-tag');
        $this->jsClickAndWait('#post_tag .tagadd');
        $this->byCssSelector('form#post #publish')->click();
        $this->waitAfterRedirect();
    }

}