<?php

use Tracy\Debugger;
use VersionPress\Tests\Selenium\SeleniumTestCase;
use VersionPress\Tests\Utils\TestConfig;
use VersionPress\Tests\Utils\TestRunnerOptions;

require_once(__DIR__ . '/../vendor/autoload.php');

$logDir = sys_get_temp_dir() . '/vp-log';
@mkdir($logDir);
Debugger::enable(Debugger::DEVELOPMENT, $logDir);

TestRunnerOptions::getInstance()->configureInstance([

    // Forces site setup either before class or the whole test suite
    "forceSetup" => ["before-class", "before-suite", "just-vp-files"],

]);

TestConfig::$defaultConfigFile = __DIR__ . '/test-config.yml';
PHPUnit_Extensions_Selenium2TestCase::shareSession(true);

if (TestRunnerOptions::getInstance()->forceSetup == "before-suite") {
    echo "Setting up site before suite";
    SeleniumTestCase::setUpSite(true);
    echo "\n";
}
