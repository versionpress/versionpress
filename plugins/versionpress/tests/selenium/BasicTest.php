<?php

class BasicTest extends FullWipeSeleniumTestCase {

    public function testWordpressWorks() {
        $this->url('wp-admin');
        $this->assertStringEndsWith('Log In', $this->title());
    }

    public function testLogin() {
        $this->url('wp-admin');
        $this->byId('user_login')->value(self::$config->getAdminName());
        usleep(100000); // wait for change focus
        $this->byId('user_pass')->value(self::$config->getAdminPassword());
        $this->byId('wp-submit')->click();
        $this->assertStringStartsWith('Dashboard', $this->title());
    }

    public function testInstallVersionPress() {
        WpAutomation::installVersionpress();
        $this->url('wp-admin/plugins.php');
        try {
            $this->byId('versionpress');
        } catch (PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
            $this->fail('VersionPress is not listed in plugins');
        }
        $this->byCssSelector('#versionpress .activate a')->click();
        $this->assertEquals('Plugin activated.', $this->byId('message')->text());
    }

    public function testInitializeVersionPress() {
        $this->url('wp-admin/admin.php?page=versionpress/administration/index.php');
        $this->byCssSelector('input[type=submit]')->click();
        $lastCommitMessage = $this->byCssSelector('#the-list td:nth-child(2)')->text();
        $this->assertEquals('[VP] Installed VersionPress', $lastCommitMessage);
    }
}