<?php

namespace VersionPress\Tests\End2End\Utils;

use VersionPress\Tests\Utils\TestConfig;

abstract class PostTypeTestWpCliWorker extends WpCliWorker implements IPostTypeTestWorker
{

    protected $testPost = [
        "post_type" => "post",
        "post_status" => "publish",
        "post_title" => "Test post",
        "post_date" => "2011-11-11 11:11:11",
        "post_content" => "Test post",
        "post_author" => 1
    ];

    private $postId;

    public function __construct(TestConfig $testConfig)
    {
        parent::__construct($testConfig);
        $this->testPost['post_type'] = $this->getPostType();
    }

    public function prepare_addPost()
    {
    }

    public function addPost()
    {
        $this->postId = $this->wpAutomation->createPost($this->testPost);
    }

    public function prepare_updatePost()
    {
    }

    public function updatePost()
    {
        $change = [
            'post_content' => 'Updated content',
            'post_title' => 'Updated ' . $this->getPostType()
        ];
        $this->wpAutomation->editPost($this->postId, $change);
    }

    public function prepare_quickEditPost()
    {
        throw new \PHPUnit_Framework_SkippedTestError('There is nothing like quick edit in the WP-CLI');
    }

    public function quickEditPost()
    {
    }

    public function prepare_trashPost()
    {
    }

    public function trashPost()
    {
        $this->wpAutomation->editPost($this->postId, ['post_status' => 'trash']);
    }

    public function prepare_untrashPost()
    {
    }

    public function untrashPost()
    {
        $this->wpAutomation->editPost($this->postId, ['post_status' => 'publish']);
    }

    public function prepare_deletePost()
    {
    }

    public function deletePost()
    {
        $this->wpAutomation->deletePost($this->postId);
    }

    public function prepare_createDraft()
    {
    }

    public function createDraft()
    {
        $draft = $this->testPost;
        // It's necessary to change the value in a local copy, not directly in the private field.
        // Some PHPUnit magic shares the data between Pages and Posts tests.
        $draft['post_status'] = 'draft';
        $this->postId = $this->wpAutomation->createPost($draft);
    }

    public function prepare_previewDraft()
    {
        throw new \PHPUnit_Framework_SkippedTestError('There is nothing like preview in the WP-CLI');
    }

    public function previewDraft()
    {
    }

    public function cleanup_previewDraft()
    {
    }

    public function prepare_publishDraft()
    {
    }

    public function publishDraft()
    {
        $this->wpAutomation->editPost($this->postId, [
            'post_date' => '2015-03-13 14:44:05',
            'post_date_gmt' => '2015-03-13 14:44:05',
            'post_content' => 'Updated content',
            'post_name' => 'test-post',
            'post_status' => 'publish'
        ]);
    }

    public function prepare_previewUnsavedPost()
    {
        throw new \PHPUnit_Framework_SkippedTestError('There is nothing like preview in the WP-CLI');
    }

    public function previewUnsavedPost()
    {
    }

    public function prepare_setFeaturedImageForUnsavedPost()
    {
        throw new \PHPUnit_Framework_SkippedTestError(
            'Featured image cannot be assigned to unexisting post using WP-CLI'
        );
    }

    public function setFeaturedImageForUnsavedPost()
    {
    }

    public function prepare_makeDraftFromUnsavedPost()
    {
        throw new \PHPUnit_Framework_SkippedTestError(
            'Featured image cannot be assigned to unexisting post using WP-CLI'
        );
    }

    public function makeDraftFromUnsavedPost()
    {
    }

    public function prepare_changeStatusOfTwoPosts()
    {
        $this->postId = [];
        $this->postId[] = $this->wpAutomation->createPost($this->testPost);
        $this->postId[] = $this->wpAutomation->createPost($this->testPost);
    }

    public function changeStatusOfTwoPosts()
    {
        $this->wpAutomation->runWpCliCommand(
            'post',
            'update',
            array_merge($this->postId, ['post_status' => 'private'])
        );
    }

    public function prepare_moveTwoPostsInTrash()
    {
        $this->postId = [];
        $this->postId[] = $this->wpAutomation->createPost($this->testPost);
        $this->postId[] = $this->wpAutomation->createPost($this->testPost);
    }

    public function moveTwoPostsInTrash()
    {
        $this->wpAutomation->runWpCliCommand('post', 'delete', $this->postId);
    }

    public function prepare_moveTwoPostsFromTrash()
    {
        $trashedPost = array_merge($this->testPost, ['post_status' => 'trash']);
        $this->postId = [];
        $this->postId[] = $this->wpAutomation->createPost($trashedPost);
        $this->postId[] = $this->wpAutomation->createPost($trashedPost);
    }

    public function moveTwoPostsFromTrash()
    {
        $this->wpAutomation->runWpCliCommand(
            'post',
            'update',
            array_merge($this->postId, ['post_status' => 'publish'])
        );
    }

    public function prepare_deleteTwoPosts()
    {
        $this->postId = [];
        $this->postId[] = $this->wpAutomation->createPost($this->testPost);
        $this->postId[] = $this->wpAutomation->createPost($this->testPost);
    }

    public function deleteTwoPosts()
    {
        $this->wpAutomation->runWpCliCommand('post', 'delete', array_merge($this->postId, ['force' => null]));
    }

    public function prepare_publishTwoPosts()
    {
        $draft = array_merge($this->testPost, ['post_status' => 'draft']);
        $this->postId = [];
        $this->postId[] = $this->wpAutomation->createPost($draft);
        $this->postId[] = $this->wpAutomation->createPost($draft);
    }

    public function publishTwoPosts()
    {
        $this->wpAutomation->runWpCliCommand(
            'post',
            'update',
            array_merge($this->postId, ['post_status' => 'publish'])
        );
    }
}
