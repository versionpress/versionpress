<?php

namespace VersionPress\Tests\End2End\Utils;

abstract class PostTypeTestSeleniumWorker extends SeleniumWorker implements IPostTypeTestWorker {

    abstract public function getPostType();

    public function prepare_addPost() {
    }

    public function addPost() {
        $this->url($this->getPostTypeScreenUrl());
        $this->prepareTestPost();

        $this->byCssSelector('form#post #publish')->click();
        $this->waitAfterRedirect();
    }

    public function prepare_updatePost() {
    }

    public function updatePost() {
        $titleField = $this->byCssSelector('form#post input#title');
        $titleField->clear();
        $titleField->value("Updated " . $this->getPostType());
        $this->setTinyMCEContent("Updated content");
        $this->byCssSelector('form#post #publish')->click();
        $this->waitAfterRedirect();
    }

    public function prepare_quickEditPost() {
    }

    public function quickEditPost() {
        $this->url($this->getPostTypeScreenUrl());
        $this->jsClickAndWait('#the-list tr:first-child .row-actions .editinline');

        $titleField = $this->byCssSelector('#the-list tr.inline-edit-row input.ptitle');
        $titleField->clear();
        $titleField->value("Quick-edited post title");
        $this->jsClickAndWait('#the-list tr.inline-edit-row a.save');
    }

    public function prepare_trashPost() {
    }

    public function trashPost() {
        $this->jsClickAndWait('#the-list tr:first-child .row-actions .submitdelete');
        $this->waitAfterRedirect();
    }

    public function prepare_untrashPost() {
    }

    public function untrashPost() {
        $this->jsClickAndWait('#message.updated a');

        $this->assertElementExists('#message.updated'); // "1 post restored from the Trash"
    }

    public function prepare_deletePost() {
        $this->trashPost();
    }

    public function deletePost() {
        $this->byCssSelector('.trash a')->click();
        $this->deletePostPermanently();
    }

    public function prepare_createDraft() {
    }

    public function createDraft() {
        $this->prepareTestPost();
        $this->byCssSelector('form#post #save-post')->click();
        $this->waitForElement('#message.updated');
    }

    public function prepare_previewDraft() {
    }

    public function previewDraft() {
        $this->setTinyMCEContent("Updated content");

        $previewLink = $this->byCssSelector('form#post #post-preview');
        $previewWindowId = $previewLink->attribute('target');
        $previewLink->click();
        $this->window($previewWindowId);
        $this->closeWindow();
        $this->window('');
    }

    public function prepare_publishDraft() {
    }

    public function publishDraft() {
        $this->byCssSelector('form#post input#publish')->click();
        $this->waitForElement('#message.updated');
    }

    public function prepare_previewUnsavedPost() {
        $this->url($this->getPostTypeScreenUrl());
        $this->prepareTestPost();
    }

    public function previewUnsavedPost() {
        $previewLink = $this->byCssSelector('form#post #post-preview');
        $previewWindowId = $previewLink->attribute('target');
        $previewLink->click();
        $this->window($previewWindowId);
        $this->closeWindow();
        $this->window('');
    }


    /**
     * @return string
     */
    protected function getPostTypeScreenUrl() {
        return 'wp-admin/edit.php?post_type=' . $this->getPostType() . '&orderby=date&order=desc';
    }

    /**
     * Deletes post permanently. Wait for the operation to complete.
     */
    private function deletePostPermanently() {
        // The CSS selector for 'Delete Permanently' is actually exactly the same as when trashing
        // the post, so is the update message, so we just use that method internally
        $this->trashPost();
    }

    /**
     * From the main page for given post type, clicks "Add new" and fills in the post title and content
     */
    private function prepareTestPost() {
        $this->byCssSelector('.edit-php #wpbody-content .wrap a.add-new-h2')->click();
        $this->waitAfterRedirect();
        $this->byCssSelector('form#post input#title')->value("Test " . $this->getPostType());
        $this->setTinyMCEContent("Test content");
    }
}
