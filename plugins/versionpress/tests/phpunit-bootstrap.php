<?php

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/selenium/SeleniumTestCase.php');
require_once(__DIR__ . '/selenium/FullWipeSeleniumTestCase.php');
require_once(__DIR__ . '/wp-cli/WpCliTestCase.php');
require_once(__DIR__ . '/TestConfig.php');
require_once(__DIR__ . '/WpAutomation.php');

NDebugger::enable(NDebugger::DEVELOPMENT, __DIR__ . '/../log');
$robotLoader = new NRobotLoader();
$robotLoader->addDirectory(__DIR__ . '/../src');
$robotLoader->setCacheStorage(new NDevNullStorage());
$robotLoader->register();

if(!is_file(__DIR__ . '/test-config.ini')) die('You have to create test-config.ini with base url for running the tests.');

$config = new TestConfig(parse_ini_file(__DIR__ . '/test-config.ini'));
SeleniumTestCase::$config = $config;
WpCliTestCase::$config = $config;

PHPUnit_Extensions_Selenium2TestCase::shareSession(true);

global $argv;
WpCliTestCase::$skipSetup = in_array("--skip-setup", $argv);