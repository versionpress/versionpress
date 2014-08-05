<?php

abstract class WordpressSeleniumTestCase extends PHPUnit_Extensions_Selenium2TestCase {
    /** @var TestConfig */
    public static $config;

    public function __construct($name = NULL, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->setBrowserUrl(self::$config->getWordpressUrl());
    }
} 