<?php

namespace VersionPress\Tests\End2End\Menus;

use VersionPress\Database\Database;
use VersionPress\Git\Reverter;
use VersionPress\Tests\End2End\Utils\SeleniumWorker;

class MenusTestSeleniumWorker extends SeleniumWorker implements IMenusTestWorker
{

    public function prepare_createMenu()
    {
    }

    public function createMenu()
    {
        $this->url(self::$wpAdminPath . '/nav-menus.php?action=edit&menu=0');
        $this->byCssSelector('#menu-name')->clear();
        $this->byCssSelector('#menu-name')->value('Test menu');
        $this->byCssSelector('#save_menu_header')->click();
        $this->waitAfterRedirect();
    }

    public function prepare_editMenu()
    {
    }

    public function editMenu()
    {
        $this->url(self::$wpAdminPath . '/nav-menus.php');
        $titleInput = $this->byCssSelector('#menu-name');
        $titleInput->clear();
        $titleInput->value('Updated menu');
        $this->byCssSelector('#save_menu_header')->click();
        $this->waitAfterRedirect();
    }

    public function prepare_addMenuItem()
    {
    }

    public function addMenuItem()
    {
        $this->url(self::$wpAdminPath . '/nav-menus.php');
        $this->addNewMenuItem();
    }

    public function prepare_editMenuItem()
    {
    }

    public function editMenuItem()
    {
        $this->url(self::$wpAdminPath . '/nav-menus.php');
        $this->byCssSelector('.menu-item:first-child .item-edit')->click();
        $this->waitForElement('.edit-menu-item-title');
        $titleInput = $this->byCssSelector('.menu-item:first-child .edit-menu-item-title');
        sleep(1);
        $titleInput->clear();
        $titleInput->value("Updated navigation label");
        $this->byCssSelector('#save_menu_header')->click();
        $this->waitAfterRedirect();
    }

    public function prepare_createMenuItemDraft()
    {
    }

    public function createMenuItemDraft()
    {
        $this->url(self::$wpAdminPath . '/nav-menus.php');
        $this->byCssSelector('a[data-type=page-all]')->click();
        $this->byCssSelector('#page-all .menu-item-checkbox:first-of-type')->click();
        $this->byCssSelector('.submit-add-to-menu')->click();
        $this->waitForAjax();
    }

    public function prepare_deleteOrphanedMenuItems()
    {
        $dbHost = self::$testConfig->testSite->dbHost;
        $dbUser = self::$testConfig->testSite->dbUser;
        $dbPassword = self::$testConfig->testSite->dbPassword;
        $dbName = self::$testConfig->testSite->dbName;
        $wpdb = new \wpdb($dbUser, $dbPassword, $dbName, $dbHost);
        $wpdb->set_prefix(self::$testConfig->testSite->dbTablePrefix);
        $deleteOrphanedFilesSeconds = Reverter::DELETE_ORPHANED_POSTS_SECONDS;
        $database = new Database($wpdb);
        $database->query(
            $wpdb->prepare(
                "UPDATE {$database->postmeta} SET meta_value = meta_value - $deleteOrphanedFilesSeconds " .
                "WHERE meta_key='_menu_item_orphaned' ORDER BY meta_id DESC LIMIT 1",
                []
            )
        );

        $pluginsDir = self::$wpAutomation->getPluginsDir();
        $updateConfigArgs = [
            'VERSIONPRESS_GUI',
            'html',
            'require' => $pluginsDir . '/versionpress/src/Cli/vp-internal.php'
        ];
        self::$wpAutomation->runWpCliCommand('vp-internal', 'update-config', $updateConfigArgs);
    }

    public function deleteOrphanedMenuItems()
    {
        $this->url(self::$wpAdminPath . '/admin.php?page=versionpress/');
        $this->acceptAlert();
        $this->byCssSelector('.vp-undo:first-of-type')->click();
        $this->waitForAjax();
    }

    public function prepare_removeMenuItem()
    {
    }

    public function removeMenuItem()
    {
        $this->url(self::$wpAdminPath . '/nav-menus.php');
        $this->byCssSelector('.menu-item:first-child .item-edit')->click();
        usleep(200 * 1000); // Wait for the UI animation
        $this->byCssSelector('.menu-item:first-child .item-delete')->click();
        usleep(1000 * 1000); // Wait for the UI animation
        $this->byCssSelector('#save_menu_header')->click();
        $this->waitAfterRedirect();
    }


    public function prepare_removeMenuItemWithChildren()
    {
        $this->url(self::$wpAdminPath . '/nav-menus.php');
        $menuId = intval($this->byId('menu')->value());

        $item = [1, 'title' => 'Parent'];
        $parentId = self::$wpAutomation->addMenuItem($menuId, "post", $item);

        $item = [1, 'title' => 'Child 1', 'parent-id' => $parentId];
        $parentId = self::$wpAutomation->addMenuItem($menuId, "post", $item);

        $item = [1, 'title' => 'Child 2', 'parent-id' => $parentId];
        self::$wpAutomation->addMenuItem($menuId, "post", $item);
    }

    public function removeMenuItemWithChildren()
    {
        $this->url(self::$wpAdminPath . '/nav-menus.php');
        $this->byCssSelector('.menu-item:nth-of-type(2) .item-edit')->click();
        usleep(200 * 1000); // Wait for the UI animation
        $this->byCssSelector('.menu-item:nth-of-type(2) .item-delete')->click();
        usleep(1000 * 1000); // Wait for the UI animation
        $this->byCssSelector('#save_menu_header')->click();
        $this->waitAfterRedirect();
    }

    public function prepare_deleteMenu()
    {
    }

    public function deleteMenu()
    {
        $this->url(self::$wpAdminPath . '/nav-menus.php');
        $this->byCssSelector('.menu-delete')->click();
        $this->acceptAlert();
        $this->waitAfterRedirect();
    }

    private function addNewMenuItem()
    {
        $this->byCssSelector('a[data-type=page-all]')->click();
        $this->byCssSelector('#page-all .menu-item-checkbox:first-of-type')->click();
        $this->byCssSelector('.submit-add-to-menu')->click();
        $this->waitForAjax();
        $this->byCssSelector('#save_menu_header')->click();
        $this->waitAfterRedirect();
    }
}
