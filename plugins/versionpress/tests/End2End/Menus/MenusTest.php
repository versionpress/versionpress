<?php

namespace VersionPress\Tests\End2End\Menus;

use VersionPress\Tests\End2End\Utils\End2EndTestCase;
use VersionPress\Tests\Utils\CommitAsserter;
use VersionPress\Tests\Utils\DBAsserter;

class MenusTest extends End2EndTestCase {

    /** @var IMenusTestWorker */
    private static $worker;

    /**
     * @test
     * @testdox New menu creates 'term/create' action
     */
    public function addingMenuCreatesTermCreateAction() {
        self::$worker->prepare_createMenu();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->createMenu();

        $commitAsserter->ignoreCommits("usermeta/create");
        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("term/create");
        $commitAsserter->assertCommitPath('A', "%vpdb%/terms/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Renaming menu creates 'term/rename' action
     * @depends addingMenuCreatesTermCreateAction
     */
    public function renamingMenuCreatesTermRenameAction() {
        self::$worker->prepare_editMenu();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->editMenu();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("term/rename");
        $commitAsserter->assertCommitPath('M', "%vpdb%/terms/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Adding menu item creates 'post/create' action
     * @depends renamingMenuCreatesTermRenameAction
     */
    public function addingMenuItemCreatesPostCreateAction() {
        self::$worker->prepare_addMenuItem();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->addMenuItem();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/create");
        $commitAsserter->assertCommitTag("VP-Post-Type", "nav_menu_item");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Editing menu item order creates 'post/edit' action.
     * @depends addingMenuItemCreatesPostCreateAction
     */
    public function editingMenuItemCreatesPostEditAction() {
        self::$worker->prepare_editMenuItem();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->editMenuItem();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/edit");
        $commitAsserter->assertCommitTag("VP-Post-Type", "nav_menu_item");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Creating menu item draft leaves not clean working directory.
     * @depends addingMenuItemCreatesPostCreateAction
     */
    public function creatingMenuItemDraftLeavesNotCleanWorkingDirectory() {
        self::$worker->prepare_createMenuItemDraft();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->createMenuItemDraft();

        $commitAsserter->assertNumCommits(0);
        $commitAsserter->assertCountOfUntrackedFiles(1);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Orphaned menu item should be deleted when doing undo
     * @depends creatingMenuItemDraftLeavesNotCleanWorkingDirectory
     */
    public function orphanedMenuItemShouldBeDeletedWhenDoingUndo() {
        self::$worker->prepare_deleteOrphanedMenuItems();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->deleteOrphanedMenuItems();

        $commitAsserter->assertNumCommits(0);
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Removing menu item creates 'post/delete' action
     * @depends editingMenuItemCreatesPostEditAction
     */
    public function removingMenuItemCreatesPostDeleteAction() {
        self::$worker->prepare_removeMenuItem();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->removeMenuItem();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/delete");
        $commitAsserter->assertCommitTag("VP-Post-Type", "nav_menu_item");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Removing menu item withChildrenUpdatesChildrensParent
     */
    public function removingMenuItemWithChildrenUpdatesChildrensParent() {
        self::$worker->prepare_removeMenuItemWithChildren();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->removeMenuItemWithChildren();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/delete");
        $commitAsserter->assertCommitTag("VP-Post-Type", "nav_menu_item");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }
    
    /**
     * @test
     * @testdox Deleting menu creates 'term/delete' action
     * @depends removingMenuItemCreatesPostDeleteAction
     */
    public function deletingMenuCreatesTermDeleteAction() {
        self::$worker->prepare_deleteMenu();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->deleteMenu();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("term/delete");
        $commitAsserter->assertCommitPath('D', "%vpdb%/terms/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

}
