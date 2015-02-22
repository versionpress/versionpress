<?php

use Nette\Caching\Storages\DevNullStorage;
use Nette\Loaders\RobotLoader;
use Tracy\Debugger;

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/selenium/SeleniumTestCase.php');
require_once(__DIR__ . '/selenium/PostTypeTestCase.php');
require_once(__DIR__ . '/end2end/EndToEndTestCase.php');
require_once(__DIR__ . '/utils/CommitAsserter.php');
require_once(__DIR__ . '/utils/ChangeInfoUtils.php');
require_once(__DIR__ . '/utils/TestConfig.php');
require_once(__DIR__ . '/utils/SeleniumConfig.php');
require_once(__DIR__ . '/utils/SiteConfig.php');
require_once(__DIR__ . '/utils/TestRunnerOptions.php');
require_once(__DIR__ . '/utils/OptionsConventionConverter.php');
require_once(__DIR__ . '/automation/WpAutomation.php');

Debugger::enable(Debugger::DEVELOPMENT, __DIR__ . '/../log');
$robotLoader = new RobotLoader();
$robotLoader->addDirectory(__DIR__ . '/../src');
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
