<?php
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/WordpressSeleniumTestCase.php');
require_once(__DIR__ . '/TestConfig.php');
require_once(__DIR__ . '/WordpressInitializer.php');

if(!is_file(__DIR__ . '/config.ini')) die('You have to create config.ini with base url for running the tests.');

$config = new TestConfig(parse_ini_file(__DIR__ . '/config.ini'));

WordpressSeleniumTestCase::$config = $config;
PHPUnit_Extensions_Selenium2TestCase::shareSession(true);

WordpressInitializer::initialize($config);

