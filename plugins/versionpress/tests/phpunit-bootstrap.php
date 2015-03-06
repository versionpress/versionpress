<?php

use Nette\Caching\Storages\DevNullStorage;
use Nette\Loaders\RobotLoader;
use Tracy\Debugger;
use VersionPress\Tests\Selenium\SeleniumTestCase;
use VersionPress\Tests\Utils\TestRunnerOptions;

require_once(__DIR__ . '/../vendor/autoload.php');
Debugger::enable(Debugger::DEVELOPMENT, __DIR__ . '/../log');

$testHelperClasses = require(__DIR__ . '/test-helper-classes.php');

$robotLoader = new RobotLoader();
$robotLoader->addDirectory(__DIR__ . '/../src');
$robotLoader->addDirectory($testHelperClasses);
$robotLoader->setCacheStorage(new DevNullStorage());
$robotLoader->register();

TestRunnerOptions::getInstance()->configureInstance(array(

    // Forces site setup either before class or the whole test suite
    "forceSetup" => array("before-class", "before-suite", "just-vp-files"),

));

PHPUnit_Extensions_Selenium2TestCase::shareSession(true);

if (TestRunnerOptions::getInstance()->forceSetup == "before-suite") {
    echo "Setting up site before suite";
    SeleniumTestCase::setUpSite(true);
}
