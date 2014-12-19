<?php

/**
 * Post tests
 *
 * @testdox Posts via web:
 */
class PostsViaWebTest extends SeleniumTestCase {

    /**
     * @test
     * @testdox New post creates `post/create` action
     */
    public function addingPostCreatesPostCreateAction() {
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->url('wp-admin/edit.php');
        $this->byCssSelector('.edit-php #wpbody-content .wrap a.add-new-h2')->click();
        $this->byCssSelector('form#post input#title')->value("Test post");
        $this->setTinyMCEContent("Test post content");

        $this->byCssSelector('form#post input#publish')->click();

        $this->waitForElement('#message.updated');

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/create");
    }

    /**
     * @test
     * @testdox Updating post creates `post/edit` action
     *
     * @depends addingPostCreatesPostCreateAction
     */
    public function updatingPostCreatesPostEditAction() {

        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->setTinyMCEContent("Updated content");
        $this->byCssSelector('form#post input#publish')->click();
        $this->waitForElement('#message.updated');

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/edit");
    }

    /**
     * @test
     * @testDox Updating post via 'quick edit' creates an equivalent commit
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

}
