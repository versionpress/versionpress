<?php
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/selenium/SeleniumTestCase.php');
require_once(__DIR__ . '/selenium/FullWipeSeleniumTestCase.php');
require_once(__DIR__ . '/TestConfig.php');
require_once(__DIR__ . '/WpAutomation.php');

if(!is_file(__DIR__ . '/test-config.ini')) die('You have to create test-config.ini with base url for running the tests.');

$config = new TestConfig(parse_ini_file(__DIR__ . '/test-config.ini'));
SeleniumTestCase::$config = $config;

PHPUnit_Extensions_Selenium2TestCase::shareSession(true);

