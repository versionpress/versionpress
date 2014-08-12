<?php

abstract class WpCliTestCase extends PHPUnit_Framework_TestCase {
    /**
     * Configuration read from `test-config.ini` and set to this variable from phpunit-bootstrap.php.
     *
     * @var TestConfig
     */
    public static $config;
} 