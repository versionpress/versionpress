<?php

namespace VersionPress\Tests\End2End\Comments;

use VersionPress\Tests\End2End\Utils\End2EndTestCase;
use VersionPress\Tests\Utils\CommitAsserter;
use VersionPress\Tests\Utils\DBAsserter;

class CommentsTest extends End2EndTestCase {

    /** @var ICommentsTestWorker */
    private static $worker;

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

        self::$worker->prepare_createCommentAwaitingModeration();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->createCommentAwaitingModeration();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/create-pending");
        $commitAsserter->assertCommitPath("A", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox New comment creates 'comment/create' action
     */
    public function addingCommentCreatesCommentCreateAction() {

        self::$worker->prepare_createComment();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->createComment();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/create");
        $commitAsserter->assertCommitTag("VP-Comment-Author", self::$testConfig->testSite->adminName);
        $commitAsserter->assertCommitPath("A", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Editing comment creates 'comment/edit' action
     * @depends addingCommentCreatesCommentCreateAction
     */
    public function editingCommentCreatesCommentEditAction() {
        self::$worker->prepare_editComment();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->editComment();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/edit");
        $commitAsserter->assertCommitTag("VP-Comment-Author", self::$testConfig->testSite->adminName);
        $commitAsserter->assertCommitPath("M", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Trashing comment creates 'comment/trash' action
     * @depends editingCommentCreatesCommentEditAction
     */
    public function trashingCommentCreatesCommentTrashAction() {
        self::$worker->prepare_trashComment();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->trashComment();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/trash");
        $commitAsserter->assertCommitPath("M", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Untrashing comment creates 'comment/untrash' action
     * @depends trashingCommentCreatesCommentTrashAction
     */
    public function untrashingCommentCreatesCommentUntrashAction() {
        self::$worker->prepare_untrashComment();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->untrashComment();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/untrash");
        $commitAsserter->assertCommitPath("M", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Deleting comment creates 'comment/delete' action
     * @depends untrashingCommentCreatesCommentUntrashAction
     */
    public function deletingCommentCreatesCommentDeleteAction() {
        self::$worker->prepare_deleteComment();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->deleteComment();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/delete");
        $commitAsserter->assertCommitPath("D", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Unapproving comment creates 'comment/unapprove' action
     * @depends addingCommentCreatesCommentCreateAction
     */
    public function unapproveComment() {
        self::$worker->prepare_unapproveComment();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->unapproveComment();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/unapprove");
        $commitAsserter->assertCommitPath("M", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Approving comment creates 'comment/approve' action
     * @depends unapproveComment
     */
    public function approveComment() {
        self::$worker->prepare_approveComment();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->approveComment();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/approve");
        $commitAsserter->assertCommitPath("M", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Marking as spam creates 'comment/spam' action
     * @depends approveComment
     */
    public function markAsSpam() {
        self::$worker->prepare_markAsSpam();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->markAsSpam();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/spam");
        $commitAsserter->assertCommitPath("M", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Marking as not spam creates 'comment/unspam' action
     * @depends markAsSpam
     */
    public function markAsNotSpam() {
        self::$worker->prepare_markAsNotSpam();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->markAsNotSpam();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/unspam");
        $commitAsserter->assertCommitPath("M", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }
}
