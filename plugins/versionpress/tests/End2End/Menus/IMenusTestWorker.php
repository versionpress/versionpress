<?php

namespace VersionPress\Tests\End2End\Menus;

use VersionPress\Tests\End2End\Utils\ITestWorker;

interface IMenusTestWorker extends ITestWorker {

    public function prepare_createMenu();
    public function createMenu();

    public function prepare_editMenu();
    public function editMenu();

    public function prepare_addMenuItem();
    public function addMenuItem();

    public function prepare_editMenuItem();
    public function editMenuItem();

    public function prepare_createMenuItemDraft();
    public function createMenuItemDraft();

    public function prepare_deleteOrphanedMenuItems();
    public function deleteOrphanedMenuItems();

    public function prepare_removeMenuItem();
    public function removeMenuItem();

    public function prepare_deleteMenu();
    public function deleteMenu();
}