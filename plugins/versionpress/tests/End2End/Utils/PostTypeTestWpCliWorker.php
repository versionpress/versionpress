<?php

namespace VersionPress\Tests\End2End\Utils;

use VersionPress\Tests\Utils\TestConfig;

abstract class PostTypeTestWpCliWorker extends WpCliWorker implements IPostTypeTestWorker {

    private $testPost = array(
        "post_type" => "post",
        "post_status" => "publish",
        "post_title" => "Test post",
        "post_date" => "2011-11-11 11:11:11",
        "post_content" => "Test post",
        "post_author" => 1
    );

    private $postId;

    public function __construct(TestConfig $testConfig) {
        parent::__construct($testConfig);
        $this->testPost['post_type'] = $this->getPostType();
    }

    public function prepare_addPost() {
    }

    public function addPost() {
        $this->postId = $this->wpAutomation->createPost($this->testPost);
    }

    public function prepare_updatePost() {
    }

    public function updatePost() {
        $change = array('post_content' => 'Edited post');
        $this->wpAutomation->editPost($this->postId, $change);
    }

    public function prepare_quickEditPost() {
        throw new \PHPUnit_Framework_SkippedTestError('There is nothing like quick edit in the WP-CLI');
    }

    public function quickEditPost() {
    }

    public function prepare_trashPost() {
    }

    public function trashPost() {
        $this->wpAutomation->editPost($this->postId, array('post_status' => 'trash'));
    }

    public function prepare_untrashPost() {
    }

    public function untrashPost() {
        $this->wpAutomation->editPost($this->postId, array('post_status' => 'publish'));
    }

    public function prepare_deletePost() {
    }

    public function deletePost() {
        $this->wpAutomation->deletePost($this->postId);
    }

    public function prepare_createDraft() {
    }

    public function createDraft() {
        $draft = $this->testPost;         // It's necessary to change the value in a local copy, not directly in the private
        $draft['post_status'] = 'draft';  // field. Some PHPUnit magic shares the data between Pages and Posts tests.
        $this->postId = $this->wpAutomation->createPost($draft);
    }

    public function prepare_previewDraft() {
        throw new \PHPUnit_Framework_SkippedTestError('There is nothing like preview in the WP-CLI');
    }

    public function previewDraft() {
    }

    public function prepare_publishDraft() {
    }

    public function publishDraft() {
        $this->wpAutomation->editPost($this->postId, array('post_status' => 'publish'));
    }
}