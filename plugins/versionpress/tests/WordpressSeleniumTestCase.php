<?php

abstract class WordpressSeleniumTestCase extends PHPUnit_Extensions_Selenium2TestCase {
    /** @var TestConfig */
    public static $config;

    public function __construct($name = NULL, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);

        $this->setBrowser(self::$config->getWebDriver());

        $capabilities = $this->getDesiredCapabilities();
        if (strlen(self::$config->getFirefoxExecutable()) > 0) {
            $capabilities["firefox_binary"] = self::$config->getFirefoxExecutable();
        }
        $this->setDesiredCapabilities($capabilities);

        $this->setBrowserUrl(self::$config->getWordpressUrl());
    }
} 