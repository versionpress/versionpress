<?php

/**
 * Pages tests
 *
 * @testdox Pagesvia web:
 */
class PagesViaWebTest extends PostTypeTestCase {

    public function getPostType() {
        return "page";
    }

    /**
     * @test
     * @testdox New page creates 'post/create' action
     */
    public function addingPageCreatesPostCreateAction() {
        $this->runAddPostTest();
    }

    /**
     * @test
     * @testdox Updating page content creates 'post/edit' action
     *
     * @depends addingPageCreatesPostCreateAction
     */
    public function updatingPageCreatesPostEditAction() {
        $this->runUpdatePostTest();
    }

    /**
     * @test
     * @testdox Updating page via quick edit creates equivalent 'post/edit' action
     *
     * @depends updatingPageCreatesPostEditAction
     */
    public function updatingPageViaQuickEditWorksEquallyWell(){
        $this->runUpdatePostViaQuickEditTest();
    }

    /**
     * @test
     * @testdox Trashing page creates 'post/trash' action
     *
     * @depends updatingPageViaQuickEditWorksEquallyWell
     */
    public function trashingPageCreatesPostTrashAction() {
        $this->runTrashPostTest();
    }

    /**
     * @test
     * @testdox Undo creates 'post/untrash' action
     *
     * @depends trashingPageCreatesPostTrashAction
     */
    public function undoCreatesPostUntrashAction() {
        $this->runUndoTrashTest();
    }

    /**
     * @test
     * @testdox Deleting page permanenly creates 'post/delete' action
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
