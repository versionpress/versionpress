<?php

namespace VersionPress\Tests\Selenium;

use VersionPress\Tests\Utils\CommitAsserter;

class RevertTest extends SeleniumTestCase {
    /**
     * @test
     * @testdox Undo reverts changes in given commit
     */
    public function undoRevertChangesInGivenCommit() {
        $this->loginIfNecessary();
        $this->createTestPost();
        $this->url('wp-admin/admin.php?page=versionpress/admin/index.php');
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->undoLastCommit();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction('versionpress/undo');
        $commitAsserter->assertCountOfAffectedFiles(1);
        $commitAsserter->assertCommitPath('D', '%vpdb%/posts/*');
        $commitAsserter->assertCleanWorkingDirectory();
    }

    /**
     * @test
     * @testdox Undo reverts only one commit
     */
    public function undoRevertsOnlyOneCommit() {
        $this->loginIfNecessary();
        self::$wpAutomation->editOption('blogname', 'Blogname for undo test');
        $this->createTestPost();
        $this->url('wp-admin/admin.php?page=versionpress/admin/index.php');
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->undoNthCommit(2);

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction('versionpress/undo');
        $commitAsserter->assertCountOfAffectedFiles(1);
        $commitAsserter->assertCommitPath('M', '%vpdb%/options.ini');
        $commitAsserter->assertCleanWorkingDirectory();
    }

    /**
     * @test
     * @testdox Undo commit can be also reverted.
     */
    public function undoCommitCanBeAlsoReverted() {
        $this->loginIfNecessary();
        $this->createTestPost();
        $this->url('wp-admin/admin.php?page=versionpress/admin/index.php');
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->undoLastCommit();
        $this->undoLastCommit();

        $commitAsserter->assertNumCommits(2);
        $commitAsserter->assertCommitAction('versionpress/undo');
        $commitAsserter->assertCountOfAffectedFiles(1);
        $commitAsserter->assertCommitPath('A', '%vpdb%/posts/*');
        $commitAsserter->assertCleanWorkingDirectory();
    }

    /**
     * @test
     * @testdox Comment deletion cannot be reverted if the commented post no longer exists
     */
    public function entityWithMissingReferenceCannotBeRestoredWithRevert() {
        $this->loginIfNecessary();
        $postId = $this->createTestPost();
        $commentId = $this->createCommentForPost($postId);
        self::$wpAutomation->deleteComment($commentId);
        self::$wpAutomation->deletePost($postId);
        $this->url('wp-admin/admin.php?page=versionpress/admin/index.php');
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->undoNthCommit(2);
        $commitAsserter->assertNumCommits(0);
        $commitAsserter->assertCleanWorkingDirectory();

    }

    /**
     * @test
     * @testdox Rollback reverts all changes made after chosen commit
     */
    public function rollbackRevertsAllChangesMadeAfterChosenCommit() {
        $this->loginIfNecessary();
        $postId = $this->createTestPost();
        $this->createCommentForPost($postId);
        self::$wpAutomation->editOption('blogname', 'Blogname for rollback test');
        $this->url('wp-admin/admin.php?page=versionpress/admin/index.php');
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->rollbackToNthCommit(4);
        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction('versionpress/rollback');
        $commitAsserter->assertCountOfAffectedFiles(3);
        $commitAsserter->assertCommitPath('D', '%vpdb%/posts/*');
        $commitAsserter->assertCommitPath('D', '%vpdb%/comments/*');
        $commitAsserter->assertCommitPath('M', '%vpdb%/options.ini');
        $commitAsserter->assertCleanWorkingDirectory();
    }

    /**
     * @test
     * @testdox Clicking on Cancel only hides the popup
     */
    public function clickingOnCancelOnlyHidesThePopup() {
        $this->loginIfNecessary();
        $this->createTestPost();
        $this->url('wp-admin/admin.php?page=versionpress/admin/index.php');
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->jsClick("#versionpress-commits-table tr:nth-child(1) a[href*=vp_undo]");
        $this->waitForAjax();
        $this->jsClick("#popover-cancel-button");
        $this->waitForAjax(); // there shouldn't be any AJAX request, but for sure...

        $commitAsserter->assertNumCommits(0);
        $commitAsserter->assertCleanWorkingDirectory();
    }

    /**
     * @test
     * @testdox OK button is disabled if the working directory is not clear
     */
    public function okButtonIsDisabledIfTheWorkingDirectoryIsNotClear() {
        $this->loginIfNecessary();
        $this->createTestPost();
        $this->url('wp-admin/admin.php?page=versionpress/admin/index.php');
        $commitAsserter = new CommitAsserter($this->gitRepository);
        touch(self::$testConfig->testSite->path . '/revert-test-file');

        $this->jsClick("#versionpress-commits-table tr:nth-child(1) a[href*=vp_undo]");
        $this->waitForAjax();
        $this->jsClick("#popover-ok-button");
        $this->waitForAjax(); // there shouldn't be any AJAX request, but for sure...

        $commitAsserter->assertNumCommits(0);
        unlink(self::$testConfig->testSite->path . '/revert-test-file');
        $commitAsserter->assertCleanWorkingDirectory();
    }

    //---------------------
    // Helper methods
    //---------------------

    private function createTestPost() {
        $post = array(
            "post_type" => "post",
            "post_status" => "publish",
            "post_title" => "Test post for revert",
            "post_date" => "2011-11-11 11:11:11",
            "post_content" => "Test post",
            "post_author" => 1
        );

        return self::$wpAutomation->createPost($post);
    }

    private function createCommentForPost($postId) {
        $comment = array(
            "comment_author" => "Mr VersionPress",
            "comment_author_email" => "versionpress@example.com",
            "comment_author_url" => "https://wordpress.org/",
            "comment_date" => "2012-12-12 12:12:12",
            "comment_content" => "Have you heard about VersionPress? It's new awesome version control plugin for WordPress.",
            "comment_approved" => 1,
            "comment_post_ID" => $postId,
        );

        return self::$wpAutomation->createComment($comment);
    }

    private function undoLastCommit() {
        $this->undoNthCommit(1);
    }

    private function undoNthCommit($whichCommit) {
        $this->jsClick("#versionpress-commits-table tr:nth-child($whichCommit) a[href*=vp_undo]");
        $this->waitForAjax();
        $this->jsClick("#popover-ok-button");
        $this->waitAfterRedirect(10000);
    }

    private function rollbackToNthCommit($whichCommit) {
        $this->jsClick("#versionpress-commits-table tr:nth-child($whichCommit) a[href*=vp_rollback]");
        $this->waitForAjax();
        $this->jsClick("#popover-ok-button");
        $this->waitAfterRedirect(10000);
    }
}
