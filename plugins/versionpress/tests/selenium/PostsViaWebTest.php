<?php

/**
 * Post tests
 *
 * @testdox Posts via web:
 */
class PostsViaWebTest extends SeleniumTestCase {

    /**
     * @test
     * @testdox New post creates 'post/create' action
     */
    public function addingPostCreatesPostCreateAction() {
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->url('wp-admin/edit.php');
        $this->prepareTestPost();

        $this->byCssSelector('form#post #publish')->click();

        $this->waitForElement('#message.updated');

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/create");
    }

    /**
     * @test
     * @testdox Updating post creates 'post/edit' action
     *
     * @depends addingPostCreatesPostCreateAction
     */
    public function updatingPostCreatesPostEditAction() {

        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->setTinyMCEContent("Updated content");
        $this->byCssSelector('form#post #publish')->click();
        $this->waitForElement('#message.updated');

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/edit");
    }

    /**
     * @test
     * @testdox Updating post via quick edit creates equivalen 'post/edit' action
     *
     * @depends updatingPostCreatesPostEditAction
     */
    public function updatingPostViaQuickEditWorksEquallyWell(){

        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->url("wp-admin/edit.php");
        $this->executeScript("jQuery('#the-list tr:first-child .row-actions .editinline').click()");
        usleep(100*1000);
        $titleField = $this->byCssSelector('#the-list tr.inline-edit-row input.ptitle');
        $titleField->clear();
        $titleField->value("Quick-edited post title");
        $this->byCssSelector('#the-list tr.inline-edit-row a.save')->click();
        usleep(1000*1000);

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitsAreEquivalent();

    }

    /**
     * @test
     * @testdox Trashing post creates 'post/trash' action
     *
     * @depends updatingPostViaQuickEditWorksEquallyWell
     */
    public function trashingPostCreatesPostTrashAction() {
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->trashPost();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/trash");
    }

    /**
     * @test
     * @testdox Undo creates 'post/untrash' action
     *
     * @depends trashingPostCreatesPostTrashAction
     */
    public function undoCreatesPostUntrashAction() {
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $undoLink = $this->byCssSelector('#message.updated a');
        $undoLink->click();

        $this->assertElementExists('#message.updated'); // "1 post restored from the Trash"

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/untrash");
    }

    /**
     * @test
     * @testdox Deleting post permanenly creates 'post/delete' action
     * @depends undoCreatesPostUntrashAction
     */
    public function deletePermanentlyCreatesPostDeleteAction() {
        $this->trashPost();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->byCssSelector('.trash a')->click();
        $this->deletePostPermanently();


        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/delete");

    }

    /**
     * @test
     * @testdox Creating draft creates 'post/draft' action
     * @depends deletePermanentlyCreatesPostDeleteAction
     */
    public function creatingDraftCreatesPostDraftAction() {
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->prepareTestPost();
        $this->byCssSelector('form#post #save-post')->click();
        $this->waitForElement('#message.updated');

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/draft");

    }

    /**
     * @test
     * @testdox Previewing draft does not create a commit
     * @depends creatingDraftCreatesPostDraftAction
     */
    public function previewingDraftDoesNotCreateCommit() {
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->setTinyMCEContent("Updated content");

        $previewLink = $this->byCssSelector('form#post #post-preview');
        $previewWindowId = $previewLink->attribute('target');
        $previewLink->click();
        $this->window($previewWindowId);
        $this->closeWindow();
        $this->window('');

        $commitAsserter->assertNumCommits(0);

    }

    /**
     * @test
     * @testdox Publishing draft creates 'post/publish' action
     * @depends previewingDraftDoesNotCreateCommit
     */
    public function publishingDraftCreatesPostPublishAction() {
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->byCssSelector('form#post input#publish')->click();
        $this->waitForElement('#message.updated');

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/publish");

    }


    //--------------------------
    // Helper methods
    //--------------------------

    /**
     * Trashes post. Waits for the operation to complete.
     */
    private function trashPost() {
        $this->executeScript("jQuery('#the-list tr:first-child .row-actions .submitdelete')[0].click()");
        $this->waitForElement('#message.updated');
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
     * From the main posts page, clicks "Add new" and fills in the post title and contents
     */
    private function prepareTestPost() {
        $this->byCssSelector('.edit-php #wpbody-content .wrap a.add-new-h2')->click();
        $this->byCssSelector('form#post input#title')->value("Test post");
        $this->setTinyMCEContent("Test post content");
    }
}
