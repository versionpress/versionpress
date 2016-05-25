<?php
namespace VersionPress\Tests\Selenium;

use Exception;
use VersionPress\Initialization\InitializationConfig;
use VersionPress\Tests\Utils\DBAsserter;
use VersionPress\Tests\Utils\TestConfig;
use VersionPress\Tests\Utils\TestRunnerOptions;
use VersionPress\Tests\Utils\WpVersionComparer;

/**
 * Tests VersionPress deactivation / reactivation / uninstallation flow.
 */
class ActivationDeactivationTest extends SeleniumTestCase
{

    /**
     * We're overriding the default setUpBeforeClass()
     */
    public static function setUpBeforeClass()
    {
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
    public function completeUninstallationOfUnactivatedPluginRemovesAllFiles()
    {
        self::installVersionPress();
        $this->url(self::$wpAdminPath . '/plugins.php');
        $this->byCssSelector('.delete a[href*=\'versionpress\']')->click();
        $this->byCssSelector('.wrap form:nth-of-type(1) input#submit')->click();

        $this->waitForElement('.plugins-php #message.updated');

        $this->assertFileNotExists(self::$wpAutomation->getAbspath() . '/wp-includes/wpdb.php.original');
        $this->assertFileNotExists(self::$wpAutomation->getPluginsDir() . '/versionpress');
        $this->assertFileNotExists(self::$testConfig->testSite->path . '/.git');
    }

    //----------------------------
    // Activation
    //----------------------------

    /**
     * @test
     * @see vp_gettext_filter_plugin_activated() Nag string is constructed here
     */
    public function successfulActivationDisplaysInitializationHintInTheActivationMessage()
    {
        self::installVersionPress();
        $this->activateVersionPress();
        $this->assertContains("VersionPress", $this->byCssSelector('#message.updated p')->text());
        $this->assertElementExists('#message.updated p a');
    }

    /**
     * @test
     * @depends successfulActivationDisplaysInitializationHintInTheActivationMessage
     */
    public function nagIsDisplayedOnSubsequentRequests()
    {
        $this->url(self::$wpAdminPath . '/index.php');
        $this->assertElementExists('.vp-activation-nag');
    }

    /**
     * @test
     * @depends nagIsDisplayedOnSubsequentRequests
     */
    public function visitingVersionPressScreenShowsInitializationInformation()
    {
        $updateConfigArgs = [
            'VERSIONPRESS_GUI',
            'html',
            'require' => self::$wpAutomation->getPluginsDir() . '/versionpress/src/Cli/vp-internal.php'
        ];
        self::$wpAutomation->runWpCliCommand('vp-internal', 'update-config', $updateConfigArgs);


        $this->url(self::$wpAdminPath . '/admin.php?page=versionpress/');
        $this->assertElementExists('#activate-versionpress-btn');
    }

    /**
     * @test
     * @depends visitingVersionPressScreenShowsInitializationInformation
     */
    public function successfulActivationRedirectsToMainVersionPressTableAndAltersWpdbClass()
    {
        $wpdbFile = self::$wpAutomation->getAbspath() . '/wp-includes/wp-db.php';
        $wpdbOriginalFile = $wpdbFile . '.original';
        $this->assertFileNotExists($wpdbOriginalFile);
        $hashBeforeInit = md5_file($wpdbFile);

        $this->byCssSelector('#activate-versionpress-btn')->click();
        $this->waitAfterRedirect(30000);
        $this->waitForElement('#versionpress-commits-table', InitializationConfig::REDIRECT_AFTER_MS + 3000);

        $hashAfterInit = md5_file($wpdbFile);
        $hashOfOriginal = md5_file($wpdbOriginalFile);
        $this->assertNotEquals($hashBeforeInit, $hashAfterInit);
        $this->assertEquals($hashBeforeInit, $hashOfOriginal);
    }

    /**
     * @test
     * @depends successfulActivationRedirectsToMainVersionPressTableAndAltersWpdbClass
     */
    public function afterActivationTheFilesystemMatchDatabase()
    {
        DBAsserter::assertFilesEqualDatabase();
    }

    //----------------------------
    // Automatic reactivation
    //----------------------------

    /**
     * @test
     *
     */
    public function versionPressRestoresMethodsInWpdbAfterReplacingWithOriginal()
    {
        $isReplacedCall = 'echo VersionPress\Initialization\WpdbReplacer::isReplaced();';
        $restoreCall = 'VersionPress\Initialization\WpdbReplacer::restoreOriginal();';


        $isReplaced = (bool)self::$wpAutomation->runWpCliCommand('eval', null, $isReplacedCall);
        $this->assertTrue($isReplaced);

        $isReplaced = (bool)self::$wpAutomation->runWpCliCommand('eval', null, $restoreCall . $isReplacedCall);
        $this->assertFalse($isReplaced);

        $isReplaced = (bool)self::$wpAutomation->runWpCliCommand('eval', null, $isReplacedCall);
        $this->assertTrue($isReplaced);
    }

    //----------------------------
    // Deactivation
    //----------------------------


    /**
     * @test
     */
    public function deactivateShowsConfirmationScreen()
    {
        $this->url(self::$wpAdminPath . '/plugins.php');
        $this->byCssSelector('.deactivate a[href*=\'versionpress\']')->click();


        if (WpVersionComparer::compare(TestConfig::createDefaultConfig()->testSite->wpVersion, '4.2-beta1') < 0) {
            $deactivationUrl = 'versionpress/admin/deactivate.php';
        } else {
            $deactivationUrl = 'versionpress%2Fadmin%2Fdeactivate.php';
        }

        $this->assertContains($deactivationUrl, $this->url());
    }

    /**
     * @test
     * @depends deactivateShowsConfirmationScreen
     */
    public function cancelDeactivationReturnsToPluginPage()
    {

        $this->byCssSelector('#cancel_deactivation')->click();

        $this->assertContains('wp-admin/plugins.php', $this->url());
        $this->assertFileExists(self::$wpAutomation->getVpdbDir() . '/.active');

    }

    /**
     * @test
     * @depends cancelDeactivationReturnsToPluginPage
     */
    public function confirmingDeactivationFullyDeactivatesVersionPress()
    {

        $this->byCssSelector('.deactivate a[href*=\'versionpress\']')->click();

        if (WpVersionComparer::compare(TestConfig::createDefaultConfig()->testSite->wpVersion, '4.2-beta1') < 0) {
            $deactivationUrl = 'versionpress/admin/deactivate.php';
        } else {
            $deactivationUrl = 'versionpress%2Fadmin%2Fdeactivate.php';
        }

        $this->assertContains($deactivationUrl, $this->url());

        $this->byCssSelector('#confirm_deactivation')->click();
        $this->assertContains('wp-admin/plugins.php', $this->url());

        $this->assertFileNotExists(self::$wpAutomation->getAbspath() . '/wp-includes/wpdb.php.original');
        $this->assertFileExists(self::$testConfig->testSite->path . '/.git');

    }

    /**
     * @test
     * @depends confirmingDeactivationFullyDeactivatesVersionPress
     */
    public function deactivateBeforeFullActivationSkipsConfirmation()
    {
        $this->byCssSelector('.activate a[href*=\'versionpress\']')->click();
        $this->byCssSelector('.deactivate a[href*=\'versionpress\']')->click();

        $this->assertContains('wp-admin/plugins.php', $this->url());
        $this->assertElementExists('.activate a[href*=\'versionpress\']');
    }


    /**
     * @test
     * @depends deactivateBeforeFullActivationSkipsConfirmation
     */
    public function completeUninstallationRemovesGitRepo()
    {

        $this->byCssSelector('.delete a[href*=\'versionpress\']')->click();
        $this->byCssSelector('.wrap form:nth-of-type(1) input#submit')->click();

        $this->waitForElement('.plugins-php #message.updated');

        $this->assertFileNotExists(self::$wpAutomation->getAbspath() . '/wp-includes/wpdb.php.original');
        $this->assertFileNotExists(self::$wpAutomation->getPluginsDir() . '/versionpress');
        $this->assertFileNotExists(self::$wpAutomation->getVpdbDir());
        $this->assertFileNotExists(self::$testConfig->testSite->path . '/.git');

    }


    //---------------------
    // Helper functions
    //---------------------

    private function activateVersionPress()
    {
        $this->url(self::$wpAdminPath . '/plugins.php');
        $this->byCssSelector('.activate a[href*=\'versionpress\']')->click();
        $this->waitAfterRedirect();
    }

    private static function installVersionPress()
    {
        try {
            self::$wpAutomation->uninstallVersionPress();
        } catch (Exception $e) {
        }

        self::$wpAutomation->copyVersionPressFiles();
    }
}
