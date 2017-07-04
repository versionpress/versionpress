<?php

namespace VersionPress\Tests\End2End\Comments;

use VersionPress\Tests\End2End\Utils\End2EndTestCase;
use VersionPress\Tests\Utils\CommitAsserter;
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

        $this->commitAsserter->reset();

        self::$worker->createCommentAwaitingModeration();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("comment/create-pending");
        $this->commitAsserter->assertCommitPath("A", "%vpdb%/comments/%VPID%.ini");
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     *
     * @test
     */
    public function spamCommentIsNotCommitted()
    {

        self::$worker->prepare_createSpamComment();

        $this->commitAsserter->reset();

        self::$worker->createSpamComment();

        $this->commitAsserter->assertNumCommits(0);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox New comment creates 'comment/create' action
     */
    public function addingCommentCreatesCommentCreateAction()
    {

        self::$worker->prepare_createComment();

        $this->commitAsserter->reset();

        self::$worker->createComment();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("comment/create");
        $this->commitAsserter->assertCommitTag("VP-Comment-Author", self::$testConfig->testSite->adminUser);
        $this->commitAsserter->assertCommitPath("A", "%vpdb%/comments/%VPID%.ini");
        $this->commitAsserter->assertCleanWorkingDirectory();
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

        $this->commitAsserter->reset();

        self::$worker->editComment();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("comment/update");
        $this->commitAsserter->assertCommitTag("VP-Comment-Author", self::$testConfig->testSite->adminUser);
        $this->commitAsserter->assertCommitPath("M", "%vpdb%/comments/%VPID%.ini");
        $this->commitAsserter->assertCleanWorkingDirectory();
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

        $this->commitAsserter->reset();

        self::$worker->trashComment();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("comment/trash");
        $this->commitAsserter->assertCommitPath("M", "%vpdb%/comments/%VPID%.ini");
        $this->commitAsserter->assertCleanWorkingDirectory();
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

        $this->commitAsserter->reset();

        self::$worker->untrashComment();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("comment/untrash");
        $this->commitAsserter->assertCommitPath("M", "%vpdb%/comments/%VPID%.ini");
        $this->commitAsserter->assertCleanWorkingDirectory();
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

        $this->commitAsserter->reset();

        self::$worker->deleteComment();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("comment/delete");
        $this->commitAsserter->assertCommitPath("D", "%vpdb%/comments/%VPID%.ini");
        $this->commitAsserter->assertCleanWorkingDirectory();
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

        $this->commitAsserter->reset();

        self::$worker->unapproveComment();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("comment/unapprove");
        $this->commitAsserter->assertCommitPath("M", "%vpdb%/comments/%VPID%.ini");
        $this->commitAsserter->assertCleanWorkingDirectory();
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

        $this->commitAsserter->reset();

        self::$worker->approveComment();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("comment/approve");
        $this->commitAsserter->assertCommitPath("M", "%vpdb%/comments/%VPID%.ini");
        $this->commitAsserter->assertCleanWorkingDirectory();
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

        $this->commitAsserter->reset();

        self::$worker->markAsSpam();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("comment/spam");
        $this->commitAsserter->assertCommitPath("M", "%vpdb%/comments/%VPID%.ini");
        $this->commitAsserter->assertCleanWorkingDirectory();
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

        $this->commitAsserter->reset();

        self::$worker->markAsNotSpam();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("comment/unspam");
        $this->commitAsserter->assertCommitPath("M", "%vpdb%/comments/%VPID%.ini");
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Editing multiple comments creates bulk action
     */
    public function editingMultipleCommentsCreatesBulkAction()
    {
        self::$worker->prepare_editTwoComments();

        $this->commitAsserter->reset();

        self::$worker->editTwoComments();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertBulkAction('comment/update', 2);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Deleting multiple comments creates bulk action
     */
    public function deletingMultipleCommentsCreatesBulkAction()
    {
        self::$worker->prepare_deleteTwoComments();

        $this->commitAsserter->reset();

        self::$worker->deleteTwoComments();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertBulkAction('comment/delete', 2);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Trashing multiple comments creates bulk action
     */
    public function trashingMultipleCommentsCreatesBulkAction()
    {
        self::$worker->prepare_moveTwoCommentsInTrash();

        $this->commitAsserter->reset();

        self::$worker->moveTwoCommentsInTrash();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertBulkAction('comment/trash', 2);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Untrashing multiple comments creates bulk action
     */
    public function untrashingMultipleCommentsCreatesBulkAction()
    {
        self::$worker->prepare_moveTwoCommentsFromTrash();

        $this->commitAsserter->reset();

        self::$worker->moveTwoCommentsFromTrash();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertBulkAction('comment/untrash', 2);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Marking multiple comments as spam creates bulk action
     */
    public function markingMultipleCommentsAsSpamCreatesBulkAction()
    {
        self::$worker->prepare_markTwoCommentsAsSpam();

        $this->commitAsserter->reset();

        self::$worker->markTwoCommentsAsSpam();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertBulkAction('comment/spam', 2);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Marking multiple spam comments as not spam creates bulk action
     */
    public function markingMultipleSpamCommentsAsNotSpamCreatesBulkAction()
    {
        self::$worker->prepare_markTwoSpamCommentsAsNotSpam();

        $this->commitAsserter->reset();

        self::$worker->markTwoSpamCommentsAsNotSpam();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertBulkAction('comment/unspam', 2);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Unapproving multiple comments creates bulk action
     */
    public function unapprovingMultipleCommentsCreatesBulkAction()
    {
        self::$worker->prepare_unapproveTwoComments();

        $this->commitAsserter->reset();

        self::$worker->unapproveTwoComments();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertBulkAction('comment/unapprove', 2);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Approving multiple comments creates bulk action
     */
    public function approvingMultipleCommentsCreatesBulkAction()
    {
        self::$worker->prepare_approveTwoComments();

        $this->commitAsserter->reset();

        self::$worker->approveTwoComments();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertBulkAction('comment/approve', 2);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Adding commentmeta to comment creates 'commentmeta/create' action
     */
    public function addingCommentmetaCreatesCommentmetaCreateAction()
    {
        self::$worker->prepare_commentmetaCreate();

        $this->commitAsserter->reset();

        self::$worker->commentmetaCreate();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("commentmeta/create", 0, true);
        $this->commitAsserter->assertCleanWorkingDirectory();
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

        $this->commitAsserter->reset();

        self::$worker->commentmetaDelete();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("commentmeta/delete", 0, true);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }
}
