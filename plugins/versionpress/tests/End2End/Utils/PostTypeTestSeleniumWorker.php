<?php

namespace VersionPress\Tests\End2End\Utils;

use VersionPress\Utils\PathUtils;

abstract class PostTypeTestSeleniumWorker extends SeleniumWorker implements IPostTypeTestWorker
{

    abstract public function getPostType();

    public function prepare_addPost()
    {
    }

    public function addPost()
    {
        $this->url($this->getPostTypeScreenUrl());
        $this->prepareTestPost();

        $this->byCssSelector('form#post #publish')->click();
        $this->waitAfterRedirect();
    }

    public function prepare_updatePost()
    {
    }

    public function updatePost()
    {
        $titleField = $this->byCssSelector('form#post input#title');
        $titleField->clear();
        $titleField->value("Updated " . $this->getPostType());
        $this->setTinyMCEContent("Updated content");
        $this->byCssSelector('form#post #publish')->click();
        $this->waitAfterRedirect();
    }

    public function prepare_quickEditPost()
    {
    }

    public function quickEditPost()
    {
        $this->url($this->getPostTypeScreenUrl());
        $this->jsClickAndWait('#the-list tr:first-child .row-actions .editinline');

        $titleField = $this->byCssSelector('#the-list tr.inline-edit-row input.ptitle');
        $titleField->clear();
        $titleField->value("Quick-edited post title");
        $this->jsClickAndWait('#the-list tr.inline-edit-row .save');
    }

    public function prepare_trashPost()
    {
    }

    public function trashPost()
    {
        $this->jsClickAndWait('#the-list tr:first-child .row-actions .submitdelete');
        $this->waitAfterRedirect();
    }

    public function prepare_untrashPost()
    {
    }

    public function untrashPost()
    {
        $this->jsClickAndWait('#message.updated a');

        $this->assertElementExists('#message.updated'); // "1 post restored from the Trash"
    }

    public function prepare_deletePost()
    {
        $this->trashPost();
    }

    public function deletePost()
    {
        $this->byCssSelector('.trash a')->click();
        $this->deletePostPermanently();
    }

    public function prepare_createDraft()
    {
    }

    public function createDraft()
    {
        $this->prepareTestPost();
        $this->byCssSelector('form#post #save-post')->click();
        $this->waitForElement('#message.updated');
    }

    public function prepare_previewDraft()
    {
    }

    public function previewDraft()
    {
        $this->setTinyMCEContent("Updated content");

        $previewLink = $this->byCssSelector('form#post #post-preview');
        $previewWindowId = $previewLink->attribute('target');
        $previewLink->click();
        $this->window($previewWindowId);
        $this->closeWindow();
        $this->window('');
    }

    public function cleanup_previewDraft()
    {
        $this->byCssSelector('#save-post')->click();
        $this->waitAfterRedirect();
    }

    public function prepare_publishDraft()
    {
    }

    public function publishDraft()
    {
        $this->setTinyMCEContent("Published content");
        $this->byCssSelector('form#post input#publish')->click();
        $this->waitForElement('#message.updated');
    }

    public function prepare_previewUnsavedPost()
    {
        $this->url($this->getPostTypeScreenUrl());
    }

    public function previewUnsavedPost()
    {
        $this->prepareTestPost();
        $previewLink = $this->byCssSelector('form#post #post-preview');
        $previewWindowId = $previewLink->attribute('target');
        $previewLink->click();
        $this->window($previewWindowId);
        $this->closeWindow();
        $this->window('');
        $this->url($this->getPostTypeScreenUrl());
        $this->acceptAlert();
    }

    public function prepare_setFeaturedImageForUnsavedPost()
    {
        $this->url($this->getPostTypeScreenUrl());
        $addNewSelector = $this->isWpVersionLowerThan('4.3-alpha1')
            ? '.edit-php #wpbody-content .wrap a.add-new-h2'
            : '.edit-php #wpbody-content .wrap a.page-title-action';
        $this->byCssSelector($addNewSelector)->click();
        $this->waitAfterRedirect();
        $attachments = json_decode(self::$wpAutomation->runWpCliCommand(
            'post',
            'list',
            ['post_type' => 'attachment', 'format' => 'json']
        ));
        if (count($attachments) > 0) {
            return;
        }

        $imagePath = PathUtils::getRelativePath(self::$testConfig->testSite->path, __DIR__ . '/../test-data/test.png');
        self::$wpAutomation->importMedia($imagePath);
    }

    public function setFeaturedImageForUnsavedPost()
    {
        $this->byCssSelector('#set-post-thumbnail')->click();
        $this->waitForAjax();
        $this->byCssSelector('.media-router .media-menu-item:nth-of-type(2)')->click();
        $this->waitForAjax();
        $this->byCssSelector('.thumbnail:first-of-type')->click();
        $this->byCssSelector('.media-button')->click();
    }

    public function prepare_makeDraftFromUnsavedPost()
    {
    }

    public function makeDraftFromUnsavedPost()
    {
        $this->byCssSelector('form#post input#title')->value('Test ' . $this->getPostType() . ' with featured image');
        $this->keys(\PHPUnit_Extensions_Selenium2TestCase_Keys::TAB);
        $this->setTinyMCEContent("Test content");
        $this->waitForElement('#sample-permalink');
        $this->waitForAjax();
        $this->url($this->getPostTypeScreenUrl());
        $this->acceptAlert();
    }

    /**
     * @return string
     */
    protected function getPostTypeScreenUrl()
    {
        return self::$wpAdminPath . '/edit.php?post_type=' . $this->getPostType() . '&orderby=date&order=desc';
    }

    /**
     * Deletes post permanently. Wait for the operation to complete.
     */
    private function deletePostPermanently()
    {
        // The CSS selector for 'Delete Permanently' is actually exactly the same as when trashing
        // the post, so is the update message, so we just use that method internally
        $this->trashPost();
    }

    /**
     * From the main page for given post type, clicks "Add new" and fills in the post title and content
     */
    protected function prepareTestPost()
    {
        $addNewSelector = $this->isWpVersionLowerThan('4.3-alpha1')
            ? '.edit-php #wpbody-content .wrap a.add-new-h2'
            : '.edit-php #wpbody-content .wrap a.page-title-action';
        $this->byCssSelector($addNewSelector)->click();
        $this->waitAfterRedirect();
        $this->byCssSelector('form#post input#title')->value("Test " . $this->getPostType());
        $this->setTinyMCEContent("Test content");
    }

    public function prepare_changeStatusOfTwoPosts()
    {
        $post = [
            "post_type" => $this->getPostType(),
            "post_status" => "publish",
            "post_title" => "Test post",
            "post_content" => "Test post",
            "post_author" => 1
        ];
        self::$wpAutomation->createPost($post);
        self::$wpAutomation->createPost($post);
    }

    public function changeStatusOfTwoPosts()
    {
        $this->url($this->getPostTypeScreenUrl());
        $this->changeStatusOfTwoLastPosts('private');
    }

    private function changeStatusOfTwoLastPosts($status)
    {
        $this->performBulkActionWithTwoLastPosts('update');

        // change status and submit
        $this->select($this->byCssSelector('#bulk-edit [name=_status]'))->selectOptionByValue($status);
        $this->jsClickAndWait('#bulk_edit');
    }

    public function prepare_moveTwoPostsInTrash()
    {
        $post = [
            "post_type" => $this->getPostType(),
            "post_status" => "publish",
            "post_title" => "Test post",
            "post_content" => "Test post",
            "post_author" => 1
        ];
        self::$wpAutomation->createPost($post, true);
        self::$wpAutomation->createPost($post);
    }

    public function moveTwoPostsInTrash()
    {
        $this->url($this->getPostTypeScreenUrl());
        $this->performBulkActionWithTwoLastPosts('trash');
    }

    public function prepare_moveTwoPostsFromTrash()
    {
    }

    public function moveTwoPostsFromTrash()
    {
        $this->url($this->getPostTypeScreenUrl() . '&post_status=trash'); // open trash
        $this->performBulkActionWithTwoLastPosts('untrash');
    }

    public function prepare_deleteTwoPosts()
    {
        $trashedPost = [
            "post_type" => $this->getPostType(),
            "post_status" => "trash",
            "post_title" => "Test post",
            "post_content" => "Test post",
            "post_author" => 1
        ];
        self::$wpAutomation->createPost($trashedPost);
        self::$wpAutomation->createPost($trashedPost);
    }

    public function deleteTwoPosts()
    {
        $this->url($this->getPostTypeScreenUrl() . '&post_status=trash'); // open trash
        $this->performBulkActionWithTwoLastPosts('delete');
    }

    public function prepare_publishTwoPosts()
    {
        $this->url($this->getPostTypeScreenUrl());
        $this->changeStatusOfTwoLastPosts('draft');
    }

    public function publishTwoPosts()
    {
        $this->changeStatusOfTwoLastPosts('publish');
    }

    private function performBulkActionWithTwoLastPosts($action)
    {
        // select two last posts
        $tableClass = $this->getPostType() . 's';
        $this->jsClick("table.$tableClass tbody tr:nth-child(1) .check-column input[type=checkbox]");
        $this->jsClick("table.$tableClass tbody tr:nth-child(2) .check-column input[type=checkbox]");
        // choose bulk edit
        $this->select($this->byId('bulk-action-selector-top'))->selectOptionByValue($action);
        $this->jsClickAndWait('#doaction');
    }
}
