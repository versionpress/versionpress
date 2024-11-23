<?php

use VersionPress\Tests\Utils\TestConfig;

require_once(__DIR__ . '/../vendor/autoload.php');

TestConfig::$defaultConfigFile = __DIR__ . '/test-config.yml';
PHPUnit\Extensions\Selenium2TestCase::shareSession(true);
