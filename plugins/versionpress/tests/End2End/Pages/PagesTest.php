<?php

namespace VersionPress\Tests\End2End\Pages;

use VersionPress\Tests\End2End\Utils\PostTypeTestCase;

class PagesTest extends PostTypeTestCase
{

    /**
     * @test
     * @testdox New page creates 'post/create' action
     */
    public function addingPageCreatesPostCreateAction()
    {
        $this->runAddPostTest();
    }

    /**
     * @test
     * @testdox Updating page content creates 'post/update' action
     *
     * @depends addingPageCreatesPostCreateAction
     */
    public function updatingPageCreatesPostEditAction()
    {
        $this->runUpdatePostTest();
    }

    /**
     * @test
     * @testdox Updating page via quick edit creates equivalent 'post/update' action
     *
     * @depends updatingPageCreatesPostEditAction
     */
    public function updatingPageViaQuickEditWorksEquallyWell()
    {
        $this->runUpdatePostViaQuickEditTest();
    }

    /**
     * @test
     * @testdox Trashing page creates 'post/trash' action
     *
     * @depends addingPageCreatesPostCreateAction
     */
    public function trashingPageCreatesPostTrashAction()
    {
        $this->runTrashPostTest();
    }

    /**
     * @test
     * @testdox Undo creates 'post/untrash' action
     *
     * @depends trashingPageCreatesPostTrashAction
     */
    public function undoCreatesPostUntrashAction()
    {
        $this->runUndoTrashTest();
    }

    /**
     * @test
     * @testdox Deleting page permanenly creates 'post/delete' action
     *
     * @depends undoCreatesPostUntrashAction
     */
    public function deletePermanentlyCreatesPostDeleteAction()
    {
        $this->runDeletePostTest();
    }

    /**
     * @test
     * @testdox Creating draft creates 'post/draft' action
     *
     * @depends deletePermanentlyCreatesPostDeleteAction
     */
    public function creatingDraftCreatesPostDraftAction()
    {
        $this->runDraftTest();
    }

    /**
     * @test
     * @testdox Previewing draft does not create a commit
     *
     * @depends creatingDraftCreatesPostDraftAction
     */
    public function previewingDraftDoesNotCreateCommit()
    {
        $this->runPreviewDraftTest();
    }

    /**
     * @test
     * @testdox Publishing draft creates 'post/publish' action
     *
     * @depends creatingDraftCreatesPostDraftAction
     */
    public function publishingDraftCreatesPostPublishAction()
    {
        $this->runPublishDraftTest();
    }

    /**
     * @test
     * @testdox Editation multiple pages creates bulk action
     */
    public function editationOfMultiplePagesCreatesBulkAction()
    {
        $this->runEditationOfMultiplePostsCreatesBulkAction();
    }

    /**
     * @test
     * @testdox Trashing multiple pages creates bulk action
     */
    public function trashingOfMultiplePagesCreatesBulkAction()
    {
        $this->runTrashingMultiplePostsCreatesBulkAction();
    }

    /**
     * @test
     * @testdox Untrashing multiple pages creates bulk action
     */
    public function untrashingOfMultiplePagesCreatesBulkAction()
    {
        $this->runUntrashingMultiplePostsCreatesBulkAction();
    }

    /**
     * @test
     * @testdox Deleting multiple pages creates bulk action
     */
    public function deletingOfMultiplePagesCreatesBulkAction()
    {
        $this->runDeletingMultiplePostsCreatesBulkAction();
    }

    /**
     * @test
     * @testdox Publishing multiple pages creates bulk action
     */
    public function publishingOfMultiplePagesCreatesBulkAction()
    {
        $this->runPublishingMultiplePostsCreatesBulkAction();
    }
}
