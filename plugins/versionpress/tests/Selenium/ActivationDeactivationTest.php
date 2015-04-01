<?php
namespace VersionPress\Tests\Selenium;

use Exception;
use VersionPress\Initialization\InitializationConfig;
use VersionPress\Tests\Utils\DBAsserter;
use VersionPress\Tests\Utils\TestRunnerOptions;

/**
 * Tests VersionPress deactivation / reactivation / uninstallation flow.
 */
class ActivationDeactivationTest extends SeleniumTestCase {

    /**
     * We're overriding the default setUpBeforeClass()
     */
    public static function setUpBeforeClass() {
        if (TestRunnerOptions::getInstance()->forceSetup == "before-class" || !self::$wpAutomation->isSiteSetUp()) {
            self::$wpAutomation->setUpSite();
        }
    }

    //----------------------------
    // Delete unactivated plugin
    //----------------------------

    /**
     * @test
     */
    public function completeUninstallationOfUnactivatedPluginRemovesAllFiles() {
        self::_installVersionPress();
        $this->url('wp-admin/plugins.php');
        $this->byCssSelector('#versionpress .delete a')->click();
        $this->byCssSelector('.wrap form:nth-of-type(1) input#submit')->click();

        $this->waitForElement('.plugins-php #message.updated');

        $this->assertFileNotExists(self::$testConfig->testSite->path . '/wp-content/db.php');
        $this->assertFileNotExists(self::$testConfig->testSite->path . '/wp-content/plugins/versionpress');
        $this->assertFileNotExists(self::$testConfig->testSite->path . '/.git');
    }

    //----------------------------
    // Activation
    //----------------------------

    /**
     * @test
     * @see vp_gettext_filter_plugin_activated() Nag string is constructed here
     */
    public function successfulActivationDisplaysInitializationHintInTheActivationMessage() {
        self::_installVersionPress();
        $this->_activateVersionPress();
        $this->assertContains("VersionPress", $this->byCssSelector('#message.updated p')->text());
        $this->assertElementExists('#message.updated p a');
    }

    /**
     * @test
     * @depends successfulActivationDisplaysInitializationHintInTheActivationMessage
     */
    public function nagIsDisplayedOnSubsequentRequests() {
        $this->url('wp-admin/index.php');
        $this->assertElementExists('.vp-activation-nag');
    }

    /**
     * @test
     * @depends nagIsDisplayedOnSubsequentRequests
     */
    public function visitingVersionPressScreenShowsInitializationInformation() {
        $this->url('wp-admin/admin.php?page=versionpress/admin/index.php');
        $this->assertElementExists('#activate-versionpress-btn');
    }

    /**
     * @test
     * @depends visitingVersionPressScreenShowsInitializationInformation
     */
    public function successfulActivationRedirectsToMainVersionPressTable() {
        $this->byCssSelector('#activate-versionpress-btn')->click();
        $this->waitAfterRedirect(15000);
        $this->waitForElement('#versionpress-commits-table', InitializationConfig::REDIRECT_AFTER_MS + 3000);
    }

    /**
     * @test
     * @depends successfulActivationRedirectsToMainVersionPressTable
     */
    public function afterDeactivationTheFilesystemMatchDatabase() {
        DBAsserter::assertFilesEqualDatabase();
    }

    //----------------------------
    // Deactivation
    //----------------------------


    /**
     * @test
     * @depends successfulActivationRedirectsToMainVersionPressTable
     */
    public function deactivateShowsConfirmationScreen() {
        $this->url('wp-admin/plugins.php');
        $this->byCssSelector('#versionpress .deactivate a')->click();

        $this->assertContains('versionpress/admin/deactivate.php', $this->url());
    }

    /**
     * @test
     * @depends deactivateShowsConfirmationScreen
     */
    public function cancelDeactivationReturnsToPluginPage() {

        $this->byCssSelector('#cancel_deactivation')->click();

        $this->assertContains('wp-admin/plugins.php', $this->url());
        $this->assertFileExists(self::$testConfig->testSite->path . '/wp-content/vpdb/.active');

    }

    /**
     * @test
     * @depends cancelDeactivationReturnsToPluginPage
     */
    public function confirmingDeactivationFullyDeactivatesVersionPress() {

        $this->byCssSelector('#versionpress .deactivate a')->click();
        $this->assertContains('versionpress/admin/deactivate.php', $this->url());

        $this->byCssSelector('#confirm_deactivation')->click();
        $this->assertContains('wp-admin/plugins.php', $this->url());

        $this->assertFileNotExists(self::$testConfig->testSite->path . '/wp-content/db.php');
        $this->assertFileNotExists(self::$testConfig->testSite->path . '/wp-content/vpdb');
        $this->assertFileExists(self::$testConfig->testSite->path . '/.git');

    }

    /**
     * @test
     * @depends confirmingDeactivationFullyDeactivatesVersionPress
     */
    public function deactivateBeforeFullActivationSkipsConfirmation() {
        $this->byCssSelector('#versionpress .activate a')->click();
        $this->byCssSelector('#versionpress .deactivate a')->click();

        $this->assertContains('wp-admin/plugins.php', $this->url());
        $this->assertElementExists('#versionpress .activate a');
    }


    /**
     * @test
     * @depends deactivateBeforeFullActivationSkipsConfirmation
     */
    public function completeUninstallationRemovesGitRepo() {

        $this->byCssSelector('#versionpress .delete a')->click();
        $this->byCssSelector('.wrap form:nth-of-type(1) input#submit')->click();

        $this->waitForElement('.plugins-php #message.updated');

        $this->assertFileNotExists(self::$testConfig->testSite->path . '/wp-content/db.php');
        $this->assertFileNotExists(self::$testConfig->testSite->path . '/wp-content/plugins/versionpress');
        $this->assertFileNotExists(self::$testConfig->testSite->path . '/.git');

    }


    //---------------------
    // Helper functions
    //---------------------

    private function _activateVersionPress() {
        $this->url('wp-admin/plugins.php');
        $this->byCssSelector('#versionpress .activate a')->click();
        $this->waitAfterRedirect();
    }

    private static function _installVersionPress() {
        try {
            self::$wpAutomation->uninstallVersionPress();
        } catch (Exception $e) {
        }

        self::$wpAutomation->copyVersionPressFiles();
    }


}
