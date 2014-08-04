<?php
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/WordpressSeleniumTestCase.php');

if(!is_file('config.ini')) die('You have to create config.ini with base url for running the tests.');

$config = parse_ini_file('config.ini');
WordpressSeleniumTestCase::$wordpressUrl = $config['wordpress-url'];
PHPUnit_Extensions_Selenium2TestCase::shareSession(true);