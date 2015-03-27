<?php

namespace VersionPress\Tests\End2End\Menus;

use VersionPress\Tests\End2End\Utils\WpCliWorker;

class MenusTestWpCliWorker extends WpCliWorker implements IMenusTestWorker {

    private $testMenuId = 0;
    private $lastInsertedItem = 0;
    private $testPagesId = 0;

    public function prepare_createMenu() {
    }
    public function createMenu() {
        $this->testMenuId = $this->wpAutomation->createMenu("Test menu");
    }

    public function prepare_editMenu() {
    }
    public function editMenu() {
        $this->wpAutomation->editMenu($this->testMenuId, "Updated menu");
    }

    public function prepare_addMenuItem() {
        $this->testPagesId = $this->createTestPage();
    }
    public function addMenuItem() {
        $item = array($this->testPagesId);
        $this->lastInsertedItem = $this->wpAutomation->addMenuItem($this->testMenuId, "post", $item);
    }

    public function prepare_editMenuItem() {
    }
    public function editMenuItem() {
        $item = array(
            'title' => 'Updated navigation label',
        );
        $this->wpAutomation->editMenuItem($this->lastInsertedItem, $item);
    }

    public function prepare_createMenuItemDraft() {
        throw new \PHPUnit_Framework_SkippedTestError('There is no way to create menu item draft in the WP-CLI');
    }
    public function createMenuItemDraft() {
    }

    public function prepare_deleteOrphanedMenuItems() {
        throw new \PHPUnit_Framework_SkippedTestError('There is no way to create menu item draft in the WP-CLI');
    }
    public function deleteOrphanedMenuItems() {
    }

    public function prepare_removeMenuItem() {
    }
    public function removeMenuItem() {
        $this->wpAutomation->removeMenuItem($this->lastInsertedItem);
    }

    public function prepare_deleteMenu() {
    }
    public function deleteMenu() {
        $this->wpAutomation->deleteMenu($this->testMenuId);
    }

    private function createTestPage() {
        $post = array(
            "post_type" => "page",
            "post_status" => "publish",
            "post_title" => "Test page for menu",
            "post_date" => "2011-11-11 11:11:11",
            "post_content" => "Test page",
            "post_author" => 1
        );

        return $this->wpAutomation->createPost($post);
    }
}