<?php

abstract class WordpressSeleniumTestCase extends PHPUnit_Extensions_Selenium2TestCase {
    public static $wordpressUrl;

    public function __construct($name = NULL, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->setBrowserUrl(self::$wordpressUrl);
    }
} 