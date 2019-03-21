<?php

use VersionPress\Tests\Utils\TestConfig;

require_once(__DIR__ . '/../vendor/autoload.php');

TestConfig::$defaultConfigFile = __DIR__ . '/test-config.yml';
PHPUnit_Extensions_Selenium2TestCase::shareSession(true);
