<?php

use Tracy\Debugger;
use VersionPress\Tests\Selenium\SeleniumTestCase;
use VersionPress\Tests\Utils\TestConfig;

require_once(__DIR__ . '/../vendor/autoload.php');

// Internal logs for things like exceptions or `Debugger::log` calls.
// Location of PHPUnit logs is dictated externally, see docker-compose.yml or CLI invocations.
$logDir = getenv('VP_TESTS_LOG_DIR') ? getenv('VP_TESTS_LOG_DIR') : sys_get_temp_dir() . '/vp-logs/.tracy';
@mkdir($logDir, 0777, true);
Debugger::enable(Debugger::DEVELOPMENT, $logDir);

TestConfig::$defaultConfigFile = __DIR__ . '/test-config.yml';
PHPUnit_Extensions_Selenium2TestCase::shareSession(true);
