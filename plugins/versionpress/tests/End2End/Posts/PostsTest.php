<?php

namespace VersionPress\Tests\End2End\Posts;

use VersionPress\Tests\End2End\Utils\PostTypeTestCase;
use VersionPress\Tests\Utils\CommitAsserter;
use VersionPress\Tests\Utils\DBAsserter;

class PostsTest extends PostTypeTestCase {

    /**
     * @test
     * @testdox New post creates 'post/create' action
     */
    public function addingPostCreatesPostCreateAction() {
        $this->runAddPostTest();
    }

    /**
     * @test
     * @testdox Updating post creates 'post/edit' action
     *
     * @depends addingPostCreatesPostCreateAction
     */
    public function updatingPostCreatesPostEditAction() {
        $this->runUpdatePostTest();
    }

    /**
     * @test
     * @testdox Updating post via quick edit creates equivalent 'post/edit' action
     *
     * @depends updatingPostCreatesPostEditAction
     */
    public function updatingPostViaQuickEditWorksEquallyWell() {
        $this->runUpdatePostViaQuickEditTest();
    }

    /**
     * @test
     * @testdox Trashing post creates 'post/trash' action
     *
     * @depends addingPostCreatesPostCreateAction
     */
    public function trashingPostCreatesPostTrashAction() {
        $this->runTrashPostTest();
    }

    /**
     * @test
     * @testdox Undo creates 'post/untrash' action
     *
     * @depends trashingPostCreatesPostTrashAction
     */
    public function undoCreatesPostUntrashAction() {
        $this->runUndoTrashTest();
    }

    /**
     * @test
     * @testdox Deleting post permanenly creates 'post/delete' action
     *
     * @depends undoCreatesPostUntrashAction
     */
    public function deletePermanentlyCreatesPostDeleteAction() {
        $this->runDeletePostTest();
    }

    /**
     * @test
     * @testdox Creating draft creates 'post/draft' action
     *
     * @depends deletePermanentlyCreatesPostDeleteAction
     */
    public function creatingDraftCreatesPostDraftAction() {
        $this->runDraftTest();
    }

    /**
     * @test
     * @testdox Previewing draft does not create a commit
     *
     * @depends creatingDraftCreatesPostDraftAction
     */
    public function previewingDraftDoesNotCreateCommit() {
        $this->runPreviewDraftTest();
    }

    /**
     * @test
     * @testdox Publishing draft creates 'post/publish' action
     *
     * @depends creatingDraftCreatesPostDraftAction
     */
    public function publishingDraftCreatesPostPublishAction() {
        $this->runPublishDraftTest();
    }

    /**
     * @test
     * @testdox Previewing unsaved post creates a draft
     *
     */
    public function previewingUnsavedPostCreatesDraft() {
        $this->runPreviewUnsavedPostTest();
    }

    /**
     * @test
     * @testdox Creating tag in the editation form creates separate commit
     */
    public function creatingTagInEditationFormCreatesSeparateCommit() {
        self::$worker->prepare_createTagInEditationForm();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->createTagInEditationForm();

        $commitAsserter->assertNumCommits(2);
        $commitAsserter->assertCommitAction('post/edit');
        $commitAsserter->assertCommitAction('term/create', 1);
        $commitAsserter->assertCommitTag("VP-Post-Type", self::$worker->getPostType());
        $commitAsserter->assertCommitTag("VP-Post-UpdatedProperties", "vp_term_taxonomy");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Setting featured image to an unsaved post does not commit
     */
    public function settingFeaturedImageToUnsavedPostDoesNotCommit() {
        $this->runSetFeaturedImageForUnsavedPostTest();
    }

    /**
     * @test
     * @testdox Turning unsaved post with featured image into a draft saves the featured image
     *
     * @depends settingFeaturedImageToUnsavedPostDoesNotCommit
     */
    public function turningUnsavedPostWithFeaturedImageIntoDraftSavesTheFeaturedImage() {
        $this->runMakeDraftFromUnsavedPostWithFeaturedImageTest();
    }
}