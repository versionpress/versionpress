<?php

namespace VersionPress\Tests\End2End\Utils;

use PHPUnit_Framework_AssertionFailedError;
use VersionPress\Tests\Utils\CommitAsserter;
use VersionPress\Tests\Utils\DBAsserter;
use VersionPress\Tests\Utils\TestConfig;
use VersionPress\Tests\Utils\WpVersionComparer;

/**
 * Contains the actual logic for post-type tests (posts tests, pages tests etc.) as a set of methods.
 * The actual test classes inherit from this and call the public methods of this class.
 *
 * Note that there are dependencies between tests - for example, the runUndoTrashTest() function expects
 * a state created by runTrashPostTest(). These dependencies are denoted in the actual actual test classes
 * using the @ depends annotation.
 *
 * Note2: helper test methods are called runXyzTest(), not testXyz() because otherwise PHPUnit would consider
 * them real tests.
 */
abstract class PostTypeTestCase extends End2EndTestCase
{

    /** @var IPostTypeTestWorker */
    protected static $worker;

    public function runAddPostTest()
    {
        self::$worker->prepare_addPost();

        $this->commitAsserter->reset();

        self::$worker->addPost();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("post/create");
        $this->commitAsserter->assertCommitTag("VP-Post-Type", $this->getPostType());
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    public function runUpdatePostTest()
    {
        self::$worker->prepare_updatePost();
        $this->commitAsserter->reset();

        self::$worker->updatePost();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("post/edit");
        $this->commitAsserter->assertCommitTag("VP-Post-Type", $this->getPostType());
        $this->commitAsserter->assertCommitTag(
            "VP-Post-UpdatedProperties",
            "post_content,post_title,post_modified,post_modified_gmt"
        );
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    public function runUpdatePostViaQuickEditTest()
    {
        self::$worker->prepare_quickEditPost();
        $this->commitAsserter->reset();

        self::$worker->quickEditPost();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitsAreEquivalent();
        $this->commitAsserter->assertCommitTag("VP-Post-Type", $this->getPostType());
        try {
            $this->commitAsserter->assertCommitTag("VP-Post-UpdatedProperties", "post_title,post_modified,post_modified_gmt");
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            // Since WP 4.2 there is a bug in WP.
            // The ping_status might be changed by the quick edit form.
            // Reported here: https://core.trac.wordpress.org/ticket/31977
            $this->commitAsserter->assertCommitTag(
                "VP-Post-UpdatedProperties",
                "post_title,ping_status,post_modified,post_modified_gmt"
            );
        }
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    public function runTrashPostTest()
    {
        self::$worker->prepare_trashPost();
        $this->commitAsserter->reset();

        self::$worker->trashPost();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("post/trash");
        $this->commitAsserter->assertCommitTag("VP-Post-Type", $this->getPostType());
        $this->commitAsserter->assertCommitTag("VP-Post-UpdatedProperties", "post_status,post_modified,post_modified_gmt");
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    public function runUndoTrashTest()
    {
        self::$worker->prepare_untrashPost();
        $this->commitAsserter->reset();

        self::$worker->untrashPost();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("post/untrash");
        $this->commitAsserter->assertCommitTag("VP-Post-Type", $this->getPostType());
        if (WpVersionComparer::compare(TestConfig::createDefaultConfig()->testSite->wpVersion, '4.5') < 0) {
            $this->commitAsserter->assertCommitTag(
                "VP-Post-UpdatedProperties",
                "post_status,post_modified,post_modified_gmt"
            );
        } else {
            $this->commitAsserter->assertCommitTag(
                "VP-Post-UpdatedProperties",
                "post_status,post_name,post_modified,post_modified_gmt"
            );
        }
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    public function runDeletePostTest()
    {
        self::$worker->prepare_deletePost();

        $this->commitAsserter->reset();

        self::$worker->deletePost();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("post/delete");
        $this->commitAsserter->assertCommitTag("VP-Post-Type", $this->getPostType());
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    public function runDraftTest()
    {
        self::$worker->prepare_createDraft();

        $this->commitAsserter->reset();

        self::$worker->createDraft();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("post/draft");
        $this->commitAsserter->assertCommitTag("VP-Post-Type", $this->getPostType());
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    public function runPreviewDraftTest()
    {
        self::$worker->prepare_previewDraft();

        $this->commitAsserter->reset();

        self::$worker->previewDraft();

        $this->commitAsserter->assertNumCommits(0);
        $this->commitAsserter->assertCleanWorkingDirectory();

        // This time we DO NOT want to assert that files equal database. It's because previewing
        // the post updates `post_date` and similar fields in the database while we don't want
        // to create a commit just to update that (seems more like a strange behavior of WP than anything else).
        // Plus we have to call a clean-up method that saves the draft because of the integrity for subsequent tests.
        self::$worker->cleanup_previewDraft();
    }

    public function runPreviewUnsavedPostTest()
    {
        self::$worker->prepare_previewUnsavedPost();

        $this->commitAsserter->reset();

        self::$worker->previewUnsavedPost();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("post/draft");
        $this->commitAsserter->assertCommitTag("VP-Post-Type", $this->getPostType());
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    public function runPublishDraftTest()
    {
        self::$worker->prepare_publishDraft();

        $this->commitAsserter->reset();

        self::$worker->publishDraft();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("post/publish");
        $this->commitAsserter->assertCommitTag("VP-Post-Type", $this->getPostType());
        $this->commitAsserter->assertCommitTag(
            "VP-Post-UpdatedProperties",
            "post_date,post_date_gmt,post_content,post_status,post_name,post_modified,post_modified_gmt"
        );
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    public function runSetFeaturedImageForUnsavedPostTest()
    {
        self::$worker->prepare_setFeaturedImageForUnsavedPost();

        $this->commitAsserter->reset();

        self::$worker->setFeaturedImageForUnsavedPost();

        $this->commitAsserter->assertNumCommits(0);
        $this->commitAsserter->assertCleanWorkingDirectory();
        // In this case we dont want to check the integrity with database. There is one extra postmeta in the database
        // representing the relation to the featured image. It will be saved in the following test.
    }


    public function runMakeDraftFromUnsavedPostWithFeaturedImageTest()
    {
        self::$worker->prepare_makeDraftFromUnsavedPost();

        $this->commitAsserter->reset();

        self::$worker->makeDraftFromUnsavedPost();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction('post/draft');
        $this->commitAsserter->assertCommitAction('postmeta/create', 0, true);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    public function runEditationOfMultiplePostsCreatesBulkAction()
    {
        self::$worker->prepare_changeStatusOfTwoPosts();

        $this->commitAsserter->reset();

        self::$worker->changeStatusOfTwoPosts();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertBulkAction('post/edit', 2);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    public function runTrashingMultiplePostsCreatesBulkAction()
    {
        self::$worker->prepare_moveTwoPostsInTrash();

        $this->commitAsserter->reset();

        self::$worker->moveTwoPostsInTrash();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertBulkAction('post/trash', 2);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    public function runUntrashingMultiplePostsCreatesBulkAction()
    {
        self::$worker->prepare_moveTwoPostsFromTrash();

        $this->commitAsserter->reset();

        self::$worker->moveTwoPostsFromTrash();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertBulkAction('post/untrash', 2);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    public function runDeletingMultiplePostsCreatesBulkAction()
    {
        self::$worker->prepare_deleteTwoPosts();

        $this->commitAsserter->reset();

        self::$worker->deleteTwoPosts();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertBulkAction('post/delete', 2);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    public function runPublishingMultiplePostsCreatesBulkAction()
    {
        self::$worker->prepare_publishTwoPosts();

        $this->commitAsserter->reset();

        self::$worker->publishTwoPosts();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertBulkAction('post/publish', 2);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    private function getPostType()
    {
        return self::$worker->getPostType();
    }
}
