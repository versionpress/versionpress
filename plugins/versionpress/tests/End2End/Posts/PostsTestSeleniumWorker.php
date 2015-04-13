<?php

namespace VersionPress\Tests\End2End\Posts;

use Nette\Utils\Random;
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
        $this->byCssSelector('#new-tag-post_tag')->value(Random::generate());
        $this->jsClickAndWait('#post_tag .tagadd');
        $this->byCssSelector('form#post #publish')->click();
        $this->waitAfterRedirect();
    }

    public function prepare_changePostFormat() {
        $this->url($this->getPostTypeScreenUrl());
        $this->prepareTestPost();

        $this->byCssSelector('form#post #publish')->click();
        $this->waitAfterRedirect();
    }

    public function changePostFormat() {
        $this->byCssSelector('input[type=radio]#post-format-quote')->click();
        $this->byCssSelector('form#post #publish')->click();
        $this->waitAfterRedirect();
    }
}