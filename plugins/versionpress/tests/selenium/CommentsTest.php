<?php

class CommentsTest extends SeleniumTestCase {

    private static $testPostId = 0;
    private static $setUpPageDone = false;
    public function setUpPage() {
        if (!self::$setUpPageDone) {
            self::$testPostId = $this->createTestPost();
            self::$setUpPageDone = true;
        }

    }


    /**
     * Note: public comments from the same IP are throttled by default (one is allowed every 15 seconds),
     * see wp_throttle_comment_flood() and check_comment_flood_db(). Before we find another workaround
     * this public test is run first. All other are done as a logged in user for which the throttling
     * is disabled.
     *
     * @see wp_throttle_comment_flood()
     * @see check_comment_flood_db()
     *
     * @test
     * @testdox Creating comment as an unauthenticated user creates 'comment/create-pending' action
     */
    public function publicCommentAwaitsModeration() {

        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->url('?p=' . self::$testPostId);

        $this->byCssSelector('#author')->value("John Tester");
        $this->byCssSelector('#email')->value("john.tester@example.com");
        $this->byCssSelector('#comment')->value("Public comment");

        $this->byCssSelector('#submit')->click();
        $this->waitForPageLoad();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/create-pending");
        $commitAsserter->assertCommitPath("A", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();

    }


    /**
     * @test
     * @testdox New comment creates 'comment/create' action
     */
    public function addingCommentCreatesCommentCreateAction() {

        $this->loginIfNecessary();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->createNewComment();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/create");
        $commitAsserter->assertCommitTag("VP-Comment-Author", self::$config->getAdminName());
        $commitAsserter->assertCommitPath("A", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();

    }

    /**
     * @test
     * @testdox Editing comment creates 'comment/edit' action
     * @depends addingCommentCreatesCommentCreateAction
     */
    public function editingCommentCreatesCommentEditAction() {

        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->clickEditLink();
        $this->waitForPageLoad();
        $this->setValue('#content', 'Updated comment by admin');
        $this->byCssSelector('#save')->click();
        $this->waitForPageLoad();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/edit");
        $commitAsserter->assertCommitTag("VP-Comment-Author", self::$config->getAdminName());
        $commitAsserter->assertCommitPath("M", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();

    }

    /**
     * @test
     * @testdox Trashing comment creates 'comment/trash' action
     * @depends editingCommentCreatesCommentEditAction
     */
    public function trashingCommentCreatesCommentTrashAction() {

        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->clickEditLink();
        $this->waitForPageLoad();
        $this->byCssSelector('#delete-action a')->click();
        $this->waitForPageLoad();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/trash");
        $commitAsserter->assertCommitPath("M", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();

    }

    /**
     * @test
     * @testdox Untrashing comment creates 'comment/untrash' action
     * @depends trashingCommentCreatesCommentTrashAction
     */
    public function untrashingCommentCreatesCommentUntrashAction() {

        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->url('wp-admin/edit-comments.php?comment_status=trash');
        $this->jsClick('#the-comment-list tr:first-child .untrash a');
        $this->url('wp-admin/edit-comments.php');

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/untrash");
        $commitAsserter->assertCommitPath("M", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();

    }

    /**
     * @test
     * @testdox Deleting comment creates 'comment/delete' action
     * @depends untrashingCommentCreatesCommentUntrashAction
     */
    public function deletingCommentCreatesCommentDeleteAction() {

        $this->jsClick('#the-comment-list tr:first-child .trash a');
        $this->url('wp-admin/edit-comments.php?comment_status=trash');

        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->jsClick('#the-comment-list tr:first-child .delete a');
        $this->url('wp-admin/edit-comments.php');

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/delete");
        $commitAsserter->assertCommitPath("D", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();

    }

    /**
     * @test
     * @testdox Unapproving comment creates 'comment/unapprove' action
     *
     * (depends intentionally missing, this test does not depend on anything)
     */
    public function unapproveComment() {
        $this->createNewComment();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->url('wp-admin/edit-comments.php');
        $this->jsClick('#the-comment-list tr:first-child .unapprove a');
        $this->refresh();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/unapprove");
        $commitAsserter->assertCommitPath("M", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();
    }

    /**
     * @test
     * @testdox Approving comment creates 'comment/approve' action
     * @depends unapproveComment
     */
    public function approveComment() {
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->url('wp-admin/edit-comments.php?comment_status=moderated');
        $this->jsClick('#the-comment-list tr:first-child .approve a');
        $this->url('wp-admin/edit-comments.php');

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/approve");
        $commitAsserter->assertCommitPath("M", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();

    }

    /**
     * @test
     * @testdox Marking as spam creates 'comment/spam' action
     * @depends approveComment
     */
    public function markAsSpam() {
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->url('wp-admin/edit-comments.php');
        $this->jsClick('#the-comment-list tr:first-child .spam a');
        $this->refresh();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/spam");
        $commitAsserter->assertCommitPath("M", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();

    }

    /**
     * @test
     * @testdox Marking as not spam creates 'comment/unspam' action
     * @depends markAsSpam
     */
    public function markAsNotSpam() {
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->url('wp-admin/edit-comments.php?comment_status=spam');
        $this->jsClick('#the-comment-list tr:first-child .unspam a');
        $this->url('wp-admin/edit-comments.php');

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/unspam");
        $commitAsserter->assertCommitPath("M", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();

    }




    // TODO Tests to create:
    // TODO Empty Trash button on trashed comments page


    //---------------------
    // Helper methods
    //---------------------
    
    private function createTestPost() {
        $post = array(
            "post_type" => "post",
            "post_status" => "publish",
            "post_title" => "Test post for comments",
            "post_date" => "2011-11-11 11:11:11",
            "post_content" => "Test post",
            "post_author" => 1
        );

        return WpAutomation::createPost($post);
    }

    /**
     * On the post view, clicks the Edit link for the first comment
     */
    private function clickEditLink() {
        $this->byCssSelector('.comment-list li:first-child .comment-edit-link')->click();
    }

    /**
     * Creates new comment for the test post and stays on that page after postback
     */
    private function createNewComment() {
        $this->url('?p=' . self::$testPostId);
        $this->byCssSelector('#comment')->value("Comment by admin");
        $this->byCssSelector('#submit')->click();
        $this->waitForPageLoad();
    }

}
