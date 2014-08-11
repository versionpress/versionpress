<?php

class FullWipeSeleniumTestCase extends SeleniumTestCase {
    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        WpAutomation::setUpSite();
    }
} 