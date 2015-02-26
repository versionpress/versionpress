<?php

namespace VersionPress\Tests\End2End\Posts;

use VersionPress\Tests\End2End\Utils\PostTypeTestCase;

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
     * @depends updatingPostViaQuickEditWorksEquallyWell
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
     * @depends undoCreatesPostUntrashAction
     */
    public function deletePermanentlyCreatesPostDeleteAction() {
        $this->runDeletePostTest();
    }

    /**
     * @test
     * @testdox Creating draft creates 'post/draft' action
     * @depends deletePermanentlyCreatesPostDeleteAction
     */
    public function creatingDraftCreatesPostDraftAction() {
        $this->runDraftTest();
    }

    /**
     * @test
     * @testdox Previewing draft does not create a commit
     * @depends creatingDraftCreatesPostDraftAction
     */
    public function previewingDraftDoesNotCreateCommit() {
        $this->runPreviewDraftTest();
    }

    /**
     * @test
     * @testdox Publishing draft creates 'post/publish' action
     * @depends previewingDraftDoesNotCreateCommit
     */
    public function publishingDraftCreatesPostPublishAction() {
        $this->runPublishDraftTest();
    }
}