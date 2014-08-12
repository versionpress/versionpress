<?php

/**
 * Tests installation, activation, deactivation and uninstallation of VersionPress.
 * Expects a site to be empty.
 */
class ActivationDeactivationTest extends SeleniumTestCase {


    public static function setUpBeforeClass() {
        WpAutomation::installVersionpress();
    }

    /**
     * @test
     */
    public function stepZero() {
        $this->loginIfNecessary();
    }

    /**
     * @test
     */
    public function activateVersionPress() {
        $this->url('wp-admin/plugins.php');
        $this->byCssSelector('#versionpress .activate a')->click();
        $this->assertEquals('Plugin activated.', $this->byId('message')->text());
    }

    /**
     * @test
     */
    public function initializeVersionPress() {
        $this->url('wp-admin/admin.php?page=versionpress/administration/index.php');
        $this->byCssSelector('input[type=submit]')->click();
        $lastCommitMessage = $this->byCssSelector('#the-list td:nth-child(2)')->text();
        $this->assertEquals('[VP] Installed VersionPress', $lastCommitMessage);
    }

    /**
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
     * @test
     */
    public function clickDisableAndConfirmUninstall() {
        $this->byCssSelector('#versionpress .deactivate a')->click();
        $this->byCssSelector('#deactivation_remove_repo')->click();

        try {
            $this->byCssSelector('#versionpress');
            $this->fail('The #versionpress element shouldn\'t exist.');
        } catch (PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
            $this->assertEquals(PHPUnit_Extensions_Selenium2TestCase_WebDriverException::NoSuchElement, $e->getCode());
            return;
        }

        // TODO: test that the `.git` folder was removed

    }

}
 