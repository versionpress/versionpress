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
        $this->byId('user_login')->value('admin');
        $this->byId('user_pass')->value('agilio');
        $this->byId('wp-submit')->click();
        $this->stringStartsWith('Dashboard', $this->title());
    }
}