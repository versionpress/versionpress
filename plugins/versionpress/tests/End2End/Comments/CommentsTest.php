<?php

namespace VersionPress\Tests\End2End\Comments;

use VersionPress\Tests\End2End\Utils\End2EndTestCase;
use VersionPress\Tests\Utils\DBAsserter;

class CommentsTest extends End2EndTestCase
{

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
    public function publicCommentAwaitsModeration()
    {

        self::$worker->prepare_createCommentAwaitingModeration();

        $commitAsserter = $this->newCommitAsserter();

        self::$worker->createCommentAwaitingModeration();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/create-pending");
        $commitAsserter->assertCommitPath("A", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     *
     * @test
     */
    public function spamCommentIsNotCommitted()
    {

        self::$worker->prepare_createSpamComment();

        $commitAsserter = $this->newCommitAsserter();

        self::$worker->createSpamComment();

        $commitAsserter->assertNumCommits(0);
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox New comment creates 'comment/create' action
     */
    public function addingCommentCreatesCommentCreateAction()
    {

        self::$worker->prepare_createComment();

        $commitAsserter = $this->newCommitAsserter();

        self::$worker->createComment();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/create");
        $commitAsserter->assertCommitTag("VP-Comment-Author", self::$testConfig->testSite->adminUser);
        $commitAsserter->assertCommitPath("A", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Editing comment creates 'comment/update' action
     * @depends addingCommentCreatesCommentCreateAction
     */
    public function editingCommentCreatesCommentEditAction()
    {
        self::$worker->prepare_editComment();

        $commitAsserter = $this->newCommitAsserter();

        self::$worker->editComment();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/update");
        $commitAsserter->assertCommitTag("VP-Comment-Author", self::$testConfig->testSite->adminUser);
        $commitAsserter->assertCommitPath("M", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Trashing comment creates 'comment/trash' action
     * @depends editingCommentCreatesCommentEditAction
     */
    public function trashingCommentCreatesCommentTrashAction()
    {
        self::$worker->prepare_trashComment();

        $commitAsserter = $this->newCommitAsserter();

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
    public function untrashingCommentCreatesCommentUntrashAction()
    {
        self::$worker->prepare_untrashComment();

        $commitAsserter = $this->newCommitAsserter();

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
    public function deletingCommentCreatesCommentDeleteAction()
    {
        self::$worker->prepare_deleteComment();

        $commitAsserter = $this->newCommitAsserter();

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
    public function unapproveComment()
    {
        self::$worker->prepare_unapproveComment();

        $commitAsserter = $this->newCommitAsserter();

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
    public function approveComment()
    {
        self::$worker->prepare_approveComment();

        $commitAsserter = $this->newCommitAsserter();

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
    public function markAsSpam()
    {
        self::$worker->prepare_markAsSpam();

        $commitAsserter = $this->newCommitAsserter();

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
    public function markAsNotSpam()
    {
        self::$worker->prepare_markAsNotSpam();

        $commitAsserter = $this->newCommitAsserter();

        self::$worker->markAsNotSpam();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/unspam");
        $commitAsserter->assertCommitPath("M", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Editing multiple comments creates bulk action
     */
    public function editingMultipleCommentsCreatesBulkAction()
    {
        self::$worker->prepare_editTwoComments();

        $commitAsserter = $this->newCommitAsserter();

        self::$worker->editTwoComments();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertBulkAction('comment/update', 2);
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Deleting multiple comments creates bulk action
     */
    public function deletingMultipleCommentsCreatesBulkAction()
    {
        self::$worker->prepare_deleteTwoComments();

        $commitAsserter = $this->newCommitAsserter();

        self::$worker->deleteTwoComments();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertBulkAction('comment/delete', 2);
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Trashing multiple comments creates bulk action
     */
    public function trashingMultipleCommentsCreatesBulkAction()
    {
        self::$worker->prepare_moveTwoCommentsInTrash();

        $commitAsserter = $this->newCommitAsserter();

        self::$worker->moveTwoCommentsInTrash();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertBulkAction('comment/trash', 2);
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Untrashing multiple comments creates bulk action
     */
    public function untrashingMultipleCommentsCreatesBulkAction()
    {
        self::$worker->prepare_moveTwoCommentsFromTrash();

        $commitAsserter = $this->newCommitAsserter();

        self::$worker->moveTwoCommentsFromTrash();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertBulkAction('comment/untrash', 2);
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Marking multiple comments as spam creates bulk action
     */
    public function markingMultipleCommentsAsSpamCreatesBulkAction()
    {
        self::$worker->prepare_markTwoCommentsAsSpam();

        $commitAsserter = $this->newCommitAsserter();

        self::$worker->markTwoCommentsAsSpam();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertBulkAction('comment/spam', 2);
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Marking multiple spam comments as not spam creates bulk action
     */
    public function markingMultipleSpamCommentsAsNotSpamCreatesBulkAction()
    {
        self::$worker->prepare_markTwoSpamCommentsAsNotSpam();

        $commitAsserter = $this->newCommitAsserter();

        self::$worker->markTwoSpamCommentsAsNotSpam();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertBulkAction('comment/unspam', 2);
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Unapproving multiple comments creates bulk action
     */
    public function unapprovingMultipleCommentsCreatesBulkAction()
    {
        self::$worker->prepare_unapproveTwoComments();

        $commitAsserter = $this->newCommitAsserter();

        self::$worker->unapproveTwoComments();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertBulkAction('comment/unapprove', 2);
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Approving multiple comments creates bulk action
     */
    public function approvingMultipleCommentsCreatesBulkAction()
    {
        self::$worker->prepare_approveTwoComments();

        $commitAsserter = $this->newCommitAsserter();

        self::$worker->approveTwoComments();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertBulkAction('comment/approve', 2);
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Adding commentmeta to comment creates 'commentmeta/create' action
     */
    public function addingCommentmetaCreatesCommentmetaCreateAction()
    {
        self::$worker->prepare_commentmetaCreate();

        $commitAsserter = $this->newCommitAsserter();

        self::$worker->commentmetaCreate();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("commentmeta/create", 0, true);
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Deleting commentmeta of comment creates 'commentmeta/delete' action
     * @depends addingCommentmetaCreatesCommentmetaCreateAction
     */
    public function deleteCommentmetaCreatesCommentmetaDeleteAction()
    {
        self::$worker->prepare_commentmetaDelete();

        $commitAsserter = $this->newCommitAsserter();

        self::$worker->commentmetaDelete();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("commentmeta/delete", 0, true);
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }
}
