<?php

/**
 * Base class for Selenium tests that use the test-config.ini configuration.
 */
abstract class SeleniumTestCase extends PHPUnit_Extensions_Selenium2TestCase {

    /**
     * Configuration read from `test-config.ini` and set to this variable from phpunit-bootstrap.php.
     *
     * @var TestConfig
     */
    public static $config;

    public function __construct($name = NULL, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);

        $this->setBrowser("firefox");

        $capabilities = $this->getDesiredCapabilities();
        if (strlen(self::$config->getFirefoxExecutable()) > 0) {
            $capabilities["firefox_binary"] = self::$config->getFirefoxExecutable();
        }
        $this->setDesiredCapabilities($capabilities);

        $this->setBrowserUrl(self::$config->getSiteUrl());
    }
} 