<?php

namespace VersionPress\Tests\End2End\Posts;

use Nette\Utils\Random;
use VersionPress\Tests\End2End\Utils\PostTypeTestSeleniumWorker;

class PostsTestSeleniumWorker extends PostTypeTestSeleniumWorker
{

    public function getPostType()
    {
        return "post";
    }

    public function prepare_createTagInEditationForm()
    {
        $this->url($this->getPostTypeScreenUrl());
        $this->jsClickAndWait('#the-list tr:first-child .row-actions .edit a');
        $this->waitAfterRedirect();
    }

    public function createTagInEditationForm()
    {
        $this->byCssSelector('#new-tag-post_tag')->value(Random::generate());
        $this->jsClickAndWait('#post_tag .tagadd');
        if ($this->elementExists('#save-post')) {
            $this->jsClickAndWait('#save-post');
        } else {
            $this->jsClickAndWait('form#post #publish');
        }
        $this->waitAfterRedirect();
    }

    public function prepare_changePostFormat()
    {
        $this->url($this->getPostTypeScreenUrl());
        $this->prepareTestPost();

        $this->byCssSelector('form#post #publish')->click();
        $this->waitAfterRedirect();
    }

    public function changePostFormat()
    {
        $this->jsClick('input[type=radio]#post-format-quote');
        $this->jsClickAndWait('form#post #publish');
        $this->waitAfterRedirect();
    }

    public function prepare_deletePostmeta()
    {
        $this->url($this->getPostTypeScreenUrl());
        $this->prepareTestPost();
        $this->byCssSelector('form#post #publish')->click();
        $this->waitForElement('#message.updated');
        $this->byCssSelector('#show-settings-link')->click();
        $this->byCssSelector('form#adv-settings #postcustom-hide')->click();
        $this->waitForElement('#metavalue');
        if ($this->elementExists("#enternew")) {
            $this->jsClickAndWait('#newmetaleft #enternew');
        }
        $this->waitForElement('#metakeyinput');
        $this->byCssSelector('#metakeyinput')->value('post_meta');
        $this->byCssSelector('#metavalue')->value(Random::generate());
        $this->byCssSelector('#newmeta-submit')->click();
        $this->waitForElement("input[id^='deletemeta']");
    }

    public function deletePostmeta()
    {
        $this->jsClickAndWait("input[id^='deletemeta']");
        $this->waitAfterRedirect();
    }
}
