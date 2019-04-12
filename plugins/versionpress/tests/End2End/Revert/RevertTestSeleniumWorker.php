<?php

namespace VersionPress\Tests\End2End\Revert;

use VersionPress\Tests\End2End\Utils\SeleniumWorker;
use VersionPress\Utils\Process;

class RevertTestSeleniumWorker extends SeleniumWorker implements IRevertTestWorker
{

    public function prepare_undoLastCommit()
    {
        $this->createTestPost();
        $this->switchToHtmlGui();
        $this->url(self::$wpAdminPath . '/admin.php?page=versionpress/');
        return [['D', '%vpdb%/posts/*']];
    }

    public function undoLastCommit()
    {
        $this->revertLastCommit();
    }

    public function prepare_undoSecondCommit()
    {
        self::$wpAutomation->editOption('blogname', 'Blogname for undo test');
        $this->createTestPost();
        $this->switchToHtmlGui();
        $this->url(self::$wpAdminPath . '/admin.php?page=versionpress/');
        return [['M', '%vpdb%/options/*']];
    }

    public function undoSecondCommit()
    {
        $this->undoNthCommit(2);
    }

    public function prepare_undoRevertedCommit()
    {
        $this->createTestPost();
        $this->switchToHtmlGui();
        $this->url(self::$wpAdminPath . '/admin.php?page=versionpress/');
        return [['A', '%vpdb%/posts/*']];
    }

    public function prepare_tryRestoreEntityWithMissingReference()
    {
        $postId = $this->createTestPost();
        $commentId = $this->createCommentForPost($postId);
        self::$wpAutomation->deleteComment($commentId);
        self::$wpAutomation->deletePost($postId);
        $this->switchToHtmlGui();
        $this->url(self::$wpAdminPath . '/admin.php?page=versionpress/');
    }

    public function tryRestoreEntityWithMissingReference()
    {
        $this->undoNthCommit(2);

    }

    public function prepare_rollbackMoreChanges()
    {
        $postId = $this->createTestPost();
        $this->createCommentForPost($postId);
        self::$wpAutomation->editOption('blogname', 'Blogname for rollback test');
        $this->switchToHtmlGui();
        $this->url(self::$wpAdminPath . '/admin.php?page=versionpress/');
        return [
            ['D', '%vpdb%/posts/*'],
            ['D', '%vpdb%/comments/*'],
            ['M', '%vpdb%/options/*'],
        ];
    }

    public function rollbackMoreChanges()
    {
        $this->rollbackToNthCommit(4);
    }

    public function prepare_clickOnCancel()
    {
        $this->createTestPost();
        $this->switchToHtmlGui();
        $this->url(self::$wpAdminPath . '/admin.php?page=versionpress/');
    }

    public function clickOnCancel()
    {
        $this->jsClick("#versionpress-commits-table tr:nth-child(1) a.vp-undo");
        $this->waitForAjax();
        $this->jsClick("#popover-cancel-button");
        $this->waitForAjax(); // there shouldn't be any AJAX request, but for sure...
    }

    public function prepare_undoWithNotCleanWorkingDirectory()
    {
        $this->createTestPost();
        $this->switchToHtmlGui();
        $this->url(self::$wpAdminPath . '/admin.php?page=versionpress/');
    }

    public function prepare_undoToTheSameState()
    {
        $this->createTestPost();
        $this->switchToHtmlGui();
        $this->url(self::$wpAdminPath . '/admin.php?page=versionpress/');
        $this->undoLastCommit();
    }

    public function prepare_rollbackToTheSameState()
    {
        $postId = $this->createTestPost();
        self::$wpAutomation->deletePost($postId);
        $this->switchToHtmlGui();
        $this->url(self::$wpAdminPath . '/admin.php?page=versionpress/');
    }

    public function rollbackToTheSameState()
    {
        $this->rollbackToNthCommit(3);
    }

    public function prepare_undoMultipleCommits()
    {
        throw new \PHPUnit_Framework_SkippedTestError("There is no way to undo multiple commits in the old GUI");
    }

    public function undoMultipleCommits()
    {

    }

    public function prepare_undoMultipleDependentCommits()
    {
        throw new \PHPUnit_Framework_SkippedTestError("There is no way to undo multiple commits in the old GUI");
    }

    public function undoMultipleDependentCommits()
    {

    }

    public function prepare_undoMultipleCommitsThatCannotBeReverted()
    {
        throw new \PHPUnit_Framework_SkippedTestError("There is no way to undo multiple commits in the old GUI");
    }

    public function undoMultipleCommitsThatCannotBeReverted()
    {

    }

    public function prepare_undoNonDbChange()
    {
        $this->switchToJavaScriptGui();
        $newFile = '/vp-file.txt';
        file_put_contents(self::$testConfig->testSite->path . '/' . $newFile, '');
        $this->url(self::$wpAdminPath . '/admin.php?page=versionpress/');
        $this->waitForElement('.CommitPanel');
        $this->jsClick('.CommitPanel-notice-toggle');
        $this->waitForElement('.CommitPanel-commit-button');
        $this->jsClick('.CommitPanel-commit-button');
        $this->waitForElement('.CommitPanel-commit-input');
        $this->byCssSelector('.CommitPanel-commit-input')->value('Manual commit');
        $this->jsClick('.CommitPanel-commit-button');
        sleep(1);

        $this->switchToHtmlGui();
        $this->url(self::$wpAdminPath . '/admin.php?page=versionpress/');

        return [['D', $newFile]];
    }

    public function undoNonDbChange()
    {
        $this->revertLastCommit();
    }

    //---------------------
    // Helper methods
    //---------------------

    private function createTestPost()
    {
        $post = [
            "post_type" => "post",
            "post_status" => "publish",
            "post_title" => "Test post for revert",
            "post_content" => "Test post",
            "post_author" => 1
        ];

        $postId = self::$wpAutomation->createPost($post);
        (new Process('echo foo'))->run();
        return $postId;
    }

    private function createCommentForPost($postId)
    {
        $comment = [
            "comment_author" => "Mr VersionPress",
            "comment_author_email" => "versionpress@example.com",
            "comment_author_url" => "https://wordpress.org/",
            "comment_date" => "2012-12-12 12:12:12",
            "comment_content" => "Have you heard about VersionPress? " .
                "It's new awesome version control plugin for WordPress.",
            "comment_approved" => 1,
            "comment_post_ID" => $postId,
        ];

        $commentId = self::$wpAutomation->createComment($comment);
        (new Process('echo foo'))->run();
        return $commentId;
    }

    private function revertLastCommit()
    {
        $this->undoNthCommit(1);
    }

    private function undoNthCommit($whichCommit)
    {
        $this->jsClick("#versionpress-commits-table tr:nth-child($whichCommit) a.vp-undo");
        $this->waitForAjax();
        $this->jsClick("#popover-ok-button");
        $this->waitAfterRedirect(10000);
    }

    private function rollbackToNthCommit($whichCommit)
    {
        $this->jsClick("#versionpress-commits-table tr:nth-child($whichCommit) a.vp-rollback");
        $this->waitForAjax();
        $this->jsClick("#popover-ok-button");
        $this->waitAfterRedirect(10000);
    }

    private function switchToHtmlGui()
    {
        $pluginsDir = self::$wpAutomation->getPluginsDir();
        $updateConfigArgs = [
            'VERSIONPRESS_GUI',
            'html',
            'require' => $pluginsDir . '/versionpress/src/Cli/vp-internal.php'
        ];
        self::$wpAutomation->runWpCliCommand('vp-internal', 'update-config', $updateConfigArgs);
    }

    private function switchToJavaScriptGui()
    {
        $pluginsDir = self::$wpAutomation->getPluginsDir();
        $updateConfigArgs = [
            'VERSIONPRESS_GUI',
            'javascript',
            'require' => $pluginsDir . '/versionpress/src/Cli/vp-internal.php'
        ];
        self::$wpAutomation->runWpCliCommand('vp-internal', 'update-config', $updateConfigArgs);
    }
}
