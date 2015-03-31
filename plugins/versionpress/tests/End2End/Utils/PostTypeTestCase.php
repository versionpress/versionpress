<?php

namespace VersionPress\Tests\End2End\Utils;

use VersionPress\Tests\Utils\CommitAsserter;
use VersionPress\Tests\Utils\DBAsserter;

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
abstract class PostTypeTestCase extends End2EndTestCase {

    /** @var IPostTypeTestWorker */
    protected static $worker;

    public function runAddPostTest() {
        self::$worker->prepare_addPost();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->addPost();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/create");
        $commitAsserter->assertCommitTag("VP-Post-Type", $this->getPostType());
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    public function runUpdatePostTest() {
        self::$worker->prepare_updatePost();
        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->updatePost();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/edit");
        $commitAsserter->assertCommitTag("VP-Post-Type", $this->getPostType());
        $commitAsserter->assertCommitTag("VP-Post-UpdatedProperties", "post_content,post_title");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    public function runUpdatePostViaQuickEditTest() {
        self::$worker->prepare_quickEditPost();
        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->quickEditPost();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitsAreEquivalent();
        $commitAsserter->assertCommitTag("VP-Post-Type", $this->getPostType());
        $commitAsserter->assertCommitTag("VP-Post-UpdatedProperties", "post_title");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    public function runTrashPostTest() {
        self::$worker->prepare_trashPost();
        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->trashPost();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/trash");
        $commitAsserter->assertCommitTag("VP-Post-Type", $this->getPostType());
        $commitAsserter->assertCommitTag("VP-Post-UpdatedProperties", "post_status");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    public function runUndoTrashTest() {
        self::$worker->prepare_untrashPost();
        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->untrashPost();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/untrash");
        $commitAsserter->assertCommitTag("VP-Post-Type", $this->getPostType());
        $commitAsserter->assertCommitTag("VP-Post-UpdatedProperties", "post_status");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    public function runDeletePostTest() {
        self::$worker->prepare_deletePost();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->deletePost();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/delete");
        $commitAsserter->assertCommitTag("VP-Post-Type", $this->getPostType());
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    public function runDraftTest() {
        self::$worker->prepare_createDraft();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->createDraft();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/draft");
        $commitAsserter->assertCommitTag("VP-Post-Type", $this->getPostType());
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    public function runPreviewDraftTest() {
        self::$worker->prepare_previewDraft();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->previewDraft();

        $commitAsserter->assertNumCommits(0);
        $commitAsserter->assertCleanWorkingDirectory();

        // This time we DO NOT want to assert that files equal database. It's because previewing
        // the post updates `post_date` and similar fields in the database while we don't want
        // to create a commit just to update that (seems more like a strange behavior of WP than anything else).
    }

    public function runPreviewUnsavedPostTest() {
        self::$worker->prepare_previewUnsavedPost();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->previewUnsavedPost();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/draft");
        $commitAsserter->assertCommitTag("VP-Post-Type", $this->getPostType());
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    public function runPublishDraftTest() {
        self::$worker->prepare_publishDraft();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->publishDraft();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/publish");
        $commitAsserter->assertCommitTag("VP-Post-Type", $this->getPostType());
        $commitAsserter->assertCommitTag("VP-Post-UpdatedProperties", "post_date,post_date_gmt,post_content,post_status,post_name");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    public function runSetFeaturedImageForUnsavedPostTest() {
        self::$worker->prepare_setFeaturedImageForUnsavedPost();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->setFeaturedImageForUnsavedPost();

        $commitAsserter->assertNumCommits(0);
        $commitAsserter->assertCleanWorkingDirectory();
        // In this case we dont want to check the integrity with database. There is one extra postmeta in the database
        // representing the relation to the featured image.
    }


    public function runMakeDraftFromUnsavedPostWithFeaturedImageTest() {
        self::$worker->prepare_makeDraftFromUnsavedPost();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->makeDraftFromUnsavedPost();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction('post/draft');
        $commitAsserter->assertCommitAction('postmeta/create', 0, true);
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    private function getPostType() {
        return self::$worker->getPostType();
    }
}
