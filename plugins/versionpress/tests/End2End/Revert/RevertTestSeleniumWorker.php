<?php

namespace VersionPress\Tests\End2End\Revert;

use VersionPress\DI\VersionPressServices;
use VersionPress\Tests\End2End\Utils\SeleniumWorker;

class RevertTestSeleniumWorker extends SeleniumWorker implements IRevertTestWorker {

    public function prepare_undoLastCommit() {
        $this->createTestPost();
        $this->switchToHtmlGui();
        $this->url('wp-admin/admin.php?page=versionpress/');
        return array(array('D', '%vpdb%/posts/*'));
    }

    public function undoLastCommit() {
        $this->_undoLastCommit();
    }

    public function prepare_undoSecondCommit() {
        self::$wpAutomation->editOption('blogname', 'Blogname for undo test');
        $this->createTestPost();
        $this->switchToHtmlGui();
        $this->url('wp-admin/admin.php?page=versionpress/');
        return array(array('M', '%vpdb%/options/*'));
    }

    public function undoSecondCommit() {
        $this->undoNthCommit(2);
    }

    public function prepare_undoRevertedCommit() {
        $this->createTestPost();
        $this->switchToHtmlGui();
        $this->url('wp-admin/admin.php?page=versionpress/');
        return array(array('A', '%vpdb%/posts/*'));
    }

    public function prepare_tryRestoreEntityWithMissingReference() {
        $postId = $this->createTestPost();
        $commentId = $this->createCommentForPost($postId);
        self::$wpAutomation->deleteComment($commentId);
        self::$wpAutomation->deletePost($postId);
        $this->switchToHtmlGui();
        $this->url('wp-admin/admin.php?page=versionpress/');
    }

    public function tryRestoreEntityWithMissingReference() {
        $this->undoNthCommit(2);

    }

    public function prepare_rollbackMoreChanges() {
        $postId = $this->createTestPost();
        $this->createCommentForPost($postId);
        self::$wpAutomation->editOption('blogname', 'Blogname for rollback test');
        $this->switchToHtmlGui();
        $this->url('wp-admin/admin.php?page=versionpress/');
        return array(
            array('D', '%vpdb%/posts/*'),
            array('D', '%vpdb%/comments/*'),
            array('M', '%vpdb%/options/*'),
        );
    }

    public function rollbackMoreChanges() {
        $this->rollbackToNthCommit(4);
    }

    public function prepare_clickOnCancel() {
        $this->createTestPost();
        $this->switchToHtmlGui();
        $this->url('wp-admin/admin.php?page=versionpress/');
    }

    public function clickOnCancel() {
        $this->jsClick("#versionpress-commits-table tr:nth-child(1) a.vp-undo");
        $this->waitForAjax();
        $this->jsClick("#popover-cancel-button");
        $this->waitForAjax(); // there shouldn't be any AJAX request, but for sure...
    }

    public function prepare_undoWithNotCleanWorkingDirectory() {
        $this->createTestPost();
        $this->switchToHtmlGui();
        $this->url('wp-admin/admin.php?page=versionpress/');
    }

    public function prepare_undoMultipleCommits() {
        throw new \PHPUnit_Framework_SkippedTestError("There is no way to undo multiple commits in the old GUI");
    }

    public function undoMultipleCommits() {

    }

    public function prepare_undoMultipleCommitsThatCannotBeReverted() {
        throw new \PHPUnit_Framework_SkippedTestError("There is no way to undo multiple commits in the old GUI");
    }

    public function undoMultipleCommitsThatCannotBeReverted() {
        
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

    private function switchToHtmlGui() {
        $updateConfigArgs = array('VERSIONPRESS_GUI', 'html', 'require' => 'wp-content/plugins/versionpress/src/Cli/vp-internal.php');
        self::$wpAutomation->runWpCliCommand('vp-internal', 'update-config', $updateConfigArgs);
    }

}
