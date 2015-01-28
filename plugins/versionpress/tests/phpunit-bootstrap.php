<?php

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/selenium/SeleniumTestCase.php');
require_once(__DIR__ . '/selenium/PostTypeTestCase.php');
require_once(__DIR__ . '/end2end/EndToEndTestCase.php');
require_once(__DIR__ . '/utils/CommitAsserter.php');
require_once(__DIR__ . '/utils/ChangeInfoUtils.php');
require_once(__DIR__ . '/TestConfig.php');
require_once(__DIR__ . '/automation/WpAutomation.php');

NDebugger::enable(NDebugger::DEVELOPMENT, __DIR__ . '/../log');
$robotLoader = new NRobotLoader();
$robotLoader->addDirectory(__DIR__ . '/../src');
$robotLoader->setCacheStorage(new NDevNullStorage());
$robotLoader->register();

if (!is_file(__DIR__ . '/test-config.ini')) die('You have to create test-config.ini with base url for running the tests.');

$config = new TestConfig(parse_ini_file(__DIR__ . '/test-config.ini'));
SeleniumTestCase::$config = $config;
EndToEndTestCase::$config = $config;

PHPUnit_Extensions_Selenium2TestCase::shareSession(true);

global $argv;
EndToEndTestCase::$skipSetup = in_array("--skip-setup", $argv);

/**
 * One accepted CLI option is --force-setup which can come in the following variants:
 *
 *  * Not specified at all => setup is not forced
 *  * --force-setup=before-class  => site will be refreshed before every test class
 *  * --force-setup=before-suite  => site will be refreshed once, before all tests are run
 *  * --force-setup (without any value, or with an unknown value)  => warning is issues
 *
 * @var array
 */

$cliOptions = getopt("", array("force-setup::"));
$setupBeforeClass = false;
$setupBeforeSuite = false;
if (!empty($cliOptions)) {

    switch ($cliOptions["force-setup"]) {
        case "before-class":
            $setupBeforeClass = true;
            break;

        case "before-suite":
            $setupBeforeSuite = true;
            break;

        default:
            echo "Incorrect value of 'force-setup' parameter, should be 'before-class' or 'before-suite'";

    }

}

$setupBeforeClass = $setupBeforeClass || getenv('VP_FORCE_SETUP') == "before-class";
$setupBeforeSuite = $setupBeforeSuite || getenv('VP_FORCE_SETUP') == "before-suite";

SeleniumTestCase::$forceSetup = $setupBeforeClass;

if ($setupBeforeSuite) {
    echo "Setting up site before suite";
    SeleniumTestCase::setUpSite(true);
}
