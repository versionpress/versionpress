<?php

namespace VersionPress\Tests\End2End\Revert;

use VersionPress\Tests\End2End\Utils\SeleniumWorker;

class RevertTestSeleniumWorker extends SeleniumWorker implements IRevertTestWorker {

    public function prepare_undoLastCommit() {
        $this->createTestPost();
        $this->url('wp-admin/admin.php?page=versionpress/admin/index.php');
    }

    public function undoLastCommit() {
        $this->_undoLastCommit();
    }

    public function prepare_undoSecondCommit() {
        self::$wpAutomation->editOption('blogname', 'Blogname for undo test');
        $this->createTestPost();
        $this->url('wp-admin/admin.php?page=versionpress/admin/index.php');
    }

    public function undoSecondCommit() {
        $this->undoNthCommit(2);
    }

    public function prepare_undoRevertedCommit() {
        $this->createTestPost();
        $this->url('wp-admin/admin.php?page=versionpress/admin/index.php');
    }

    public function prepare_tryRestoreEntityWithMissingReference() {
        $postId = $this->createTestPost();
        $commentId = $this->createCommentForPost($postId);
        self::$wpAutomation->deleteComment($commentId);
        self::$wpAutomation->deletePost($postId);
        $this->url('wp-admin/admin.php?page=versionpress/admin/index.php');
    }

    public function tryRestoreEntityWithMissingReference() {
        $this->undoNthCommit(2);

    }

    public function prepare_rollbackMoreChanges() {
        $postId = $this->createTestPost();
        $this->createCommentForPost($postId);
        self::$wpAutomation->editOption('blogname', 'Blogname for rollback test');
        $this->url('wp-admin/admin.php?page=versionpress/admin/index.php');
    }

    public function rollbackMoreChanges() {
        $this->rollbackToNthCommit(4);
    }

    public function prepare_clickOnCancel() {
        $this->createTestPost();
        $this->url('wp-admin/admin.php?page=versionpress/admin/index.php');
    }

    public function clickOnCancel() {
        $this->jsClick("#versionpress-commits-table tr:nth-child(1) a.vp-undo");
        $this->waitForAjax();
        $this->jsClick("#popover-cancel-button");
        $this->waitForAjax(); // there shouldn't be any AJAX request, but for sure...
    }

    public function prepare_undoWithNotCleanWorkingDirectory() {
        $this->createTestPost();
        $this->url('wp-admin/admin.php?page=versionpress/admin/index.php');
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

    private function _undoLastCommit() {
        $this->undoNthCommit(1);
    }

    private function undoNthCommit($whichCommit) {
        $this->jsClick("#versionpress-commits-table tr:nth-child($whichCommit) a.vp-undo");
        $this->waitForAjax();
        $this->jsClick("#popover-ok-button");
        $this->waitAfterRedirect(10000);
    }

    private function rollbackToNthCommit($whichCommit) {
        $this->jsClick("#versionpress-commits-table tr:nth-child($whichCommit) a.vp-rollback");
        $this->waitForAjax();
        $this->jsClick("#popover-ok-button");
        $this->waitAfterRedirect(10000);
    }

}