<?php

namespace VersionPress\Tests\End2End\Menus;

use VersionPress\Tests\End2End\Utils\End2EndTestCase;
use VersionPress\Tests\Utils\CommitAsserter;
use VersionPress\Tests\Utils\DBAsserter;

class MenusTest extends End2EndTestCase
{

    /** @var IMenusTestWorker */
    private static $worker;

    /**
     * @test
     * @testdox New menu creates 'term/create' action
     */
    public function addingMenuCreatesTermCreateAction()
    {
        self::$worker->prepare_createMenu();

        $this->commitAsserter->reset();

        self::$worker->createMenu();

        $this->commitAsserter->ignoreCommits("usermeta/create");
        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("term/create");
        $this->commitAsserter->assertCommitPath('A', "%vpdb%/terms/%VPID%.ini");
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Renaming menu creates 'term/rename' action
     * @depends addingMenuCreatesTermCreateAction
     */
    public function renamingMenuCreatesTermRenameAction()
    {
        self::$worker->prepare_editMenu();

        $this->commitAsserter->reset();

        self::$worker->editMenu();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("term/rename");
        $this->commitAsserter->assertCommitPath('M', "%vpdb%/terms/%VPID%.ini");
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Adding menu item creates 'post/create' action
     * @depends renamingMenuCreatesTermRenameAction
     */
    public function addingMenuItemCreatesPostCreateAction()
    {
        self::$worker->prepare_addMenuItem();

        $this->commitAsserter->reset();

        self::$worker->addMenuItem();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("post/create");
        $this->commitAsserter->assertCommitTag("VP-Post-Type", "nav_menu_item");
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Editing menu item order creates 'post/update' action.
     * @depends addingMenuItemCreatesPostCreateAction
     */
    public function editingMenuItemCreatesPostEditAction()
    {
        self::$worker->prepare_editMenuItem();

        $this->commitAsserter->reset();

        self::$worker->editMenuItem();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("post/update");
        $this->commitAsserter->assertCommitTag("VP-Post-Type", "nav_menu_item");
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Creating menu item draft leaves not clean working directory.
     * @depends addingMenuItemCreatesPostCreateAction
     */
    public function creatingMenuItemDraftLeavesNotCleanWorkingDirectory()
    {
        self::$worker->prepare_createMenuItemDraft();

        $this->commitAsserter->reset();

        self::$worker->createMenuItemDraft();

        $this->commitAsserter->assertNumCommits(0);
        $this->commitAsserter->assertCountOfUntrackedFiles(1);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Orphaned menu item should be deleted when doing undo
     * @depends creatingMenuItemDraftLeavesNotCleanWorkingDirectory
     */
    public function orphanedMenuItemShouldBeDeletedWhenDoingUndo()
    {
        self::$worker->prepare_deleteOrphanedMenuItems();

        $this->commitAsserter->reset();

        self::$worker->deleteOrphanedMenuItems();

        $this->commitAsserter->assertNumCommits(0);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Removing menu item creates 'post/delete' action
     * @depends editingMenuItemCreatesPostEditAction
     */
    public function removingMenuItemCreatesPostDeleteAction()
    {
        self::$worker->prepare_removeMenuItem();

        $this->commitAsserter->reset();

        self::$worker->removeMenuItem();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("post/delete");
        $this->commitAsserter->assertCommitTag("VP-Post-Type", "nav_menu_item");
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Removing menu item withChildrenUpdatesChildrensParent
     */
    public function removingMenuItemWithChildrenUpdatesChildrensParent()
    {
        self::$worker->prepare_removeMenuItemWithChildren();

        $this->commitAsserter->reset();

        self::$worker->removeMenuItemWithChildren();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("post/delete");
        $this->commitAsserter->assertCommitTag("VP-Post-Type", "nav_menu_item");
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Deleting menu creates 'term/delete' action
     * @depends removingMenuItemCreatesPostDeleteAction
     */
    public function deletingMenuCreatesTermDeleteAction()
    {
        self::$worker->prepare_deleteMenu();

        $this->commitAsserter->reset();

        self::$worker->deleteMenu();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("term/delete");
        $this->commitAsserter->assertCommitPath('D', "%vpdb%/terms/%VPID%.ini");
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }
}
