<?php

class BasicTests extends WordpressSeleniumTestCase {
    public function setUp() {
        $this->setBrowser('firefox');
    }

    public function testWordpressWorks() {
        $this->url('wp-admin');
        $this->assertStringEndsWith('Log In', $this->title());
    }

    public function testLogin() {
        $this->url('wp-admin');
        $this->byId('user_login')->value(self::$config->getAdminName());
        $this->byId('user_pass')->value(self::$config->getAdminPassword());
        $this->byId('wp-submit')->click();
        $this->assertStringStartsWith('Dashboard', $this->title());
    }
}