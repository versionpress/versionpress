<?php

/**
 * Tests VersionPress deactivation / uninstallation.
 * Expects a site to be created but empty.
 */
class DeactivationTest extends SeleniumTestCase {

    public static function setUpBeforeClass() {
        WpAutomation::installVersionPress();
    }

    /**
     * @test
     */
    public function stepZero() {
        $this->loginIfNecessary();
        $this->_activateVersionPress();
        $this->_initializeVersionPress();
    }


    /**
     * Tests the "Cancel" button
     *
     * @test
     */
    public function clickDisableButThenCancel() {
        $this->url('wp-admin/plugins.php');
        $this->byCssSelector('#versionpress .deactivate a')->click();

        $this->assertContains('versionpress/administration/deactivate.php', $this->url());

        $this->byCssSelector('#deactivation_canceled')->click();

        $this->assertContains('wp-admin/plugins.php', $this->url());
    }

    /**
     * Tests the "Uninstall and REMOVE repository" button
     *
     * @test
     */
    public function clickDisableAndConfirmUninstall_RemoveRepository() {
        $this->byCssSelector('#versionpress .deactivate a')->click();
        $this->byCssSelector('#deactivation_remove_repo')->click();

        try {
            $this->byCssSelector('#versionpress');
            $this->fail('The #versionpress element shouldn\'t exist.');
        } catch (PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
            $this->assertEquals(PHPUnit_Extensions_Selenium2TestCase_WebDriverException::NoSuchElement, $e->getCode());
            return;
        }

        $this->assertFileNotExists(self::$config->getSitePath() . '/.git');
        $this->assertFileNotExists(self::$config->getSitePath() . '/wp-content/db.php');

    }

    /**
     * Tests the "Uninstall and KEEP repository" button.
     *
     * @test
     */
    public function clickDisableAndConfirmUninstall_KeepRepository() {

        // We must set up VP again
        WpAutomation::installVersionPress();
        $this->_activateVersionPress();
        $this->_initializeVersionPress();

        $this->url('wp-admin/plugins.php');
        $this->byCssSelector('#versionpress .deactivate a')->click();
        $this->byCssSelector('#deactivation_keep_repo')->click();

        try {
            $this->byCssSelector('#versionpress');
            $this->fail('The #versionpress element shouldn\'t exist.');
        } catch (PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
            $this->assertEquals(PHPUnit_Extensions_Selenium2TestCase_WebDriverException::NoSuchElement, $e->getCode());
            return;
        }

        $this->assertFileNotExists(self::$config->getSitePath() . '/wp-content/db.php');
        $this->assertFileExists(self::$config->getSitePath() . '/.git');

    }



    //---------------------
    // Helper functions
    //---------------------

    private function _activateVersionPress()
    {
        $this->url('wp-admin/plugins.php');
        $this->byCssSelector('#versionpress .activate a')->click();
    }

    private function _initializeVersionPress()
    {
        $this->url('wp-admin/admin.php?page=versionpress/administration/index.php');
        $this->byCssSelector('input[type=submit]')->click();
    }

}
 