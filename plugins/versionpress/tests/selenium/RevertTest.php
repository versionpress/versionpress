<?php

class RevertTest extends SeleniumTestCase {
    /**
     * @test
     * @testdox Undo reverts only one commit
     */
    public function undoRevertsOnlyOneCommit() {
        $this->loginIfNecessary();
        $this->createTestPost();
        $this->url('wp-admin/admin.php?page=versionpress/admin/index.php');
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->undoLastCommit();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction('versionpress/undo');
        $commitAsserter->assertCommitPath('D', '%vpdb%/posts/*');
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

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction('versionpress/undo');
        $commitAsserter->assertCommitPath('A', '%vpdb%/posts/*');
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

        return WpAutomation::createPost($post);
    }

    private function undoLastCommit() {
        $this->jsClick("#versionpress-commits-table tr:first-child a[href*=vp_undo]");
    }
}
