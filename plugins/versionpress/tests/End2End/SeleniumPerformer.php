<?php

namespace VersionPress\Tests\End2End;

use PHPUnit_Extensions_Selenium2TestCase_SessionStrategy_Isolated;
use PHPUnit_Extensions_Selenium2TestCase_SessionStrategy_Shared;
use PHPUnit_Extensions_Selenium2TestCase_URL;
use VersionPress\Tests\Utils\TestConfig;

class SeleniumPerformer implements ITestPerformer {
    /** @var \PHPUnit_Extensions_Selenium2TestCase_Session */
    protected $session;

    /** @var \PHPUnit_Extensions_Selenium2TestCase_Session */
    private static $sharedSession;

    public function __construct(TestConfig $testConfig) {
        if (!self::$sharedSession) {
            self::startSession($testConfig);
        }

        $this->session = self::$sharedSession;
    }

    private static function startSession(TestConfig $testConfig) {
        $parameters = array(
            'host' => 'localhost',
            'port' => 4444,
            'browser' => NULL,
            'desiredCapabilities' => array(),
            'seleniumServerRequestsTimeout' => 60,
            'browserName' => 'firefox',
            'browserUrl' => new PHPUnit_Extensions_Selenium2TestCase_URL('')
        );

        if (isset($testConfig->seleniumConfig->firefoxBinary)) {
            $parameters['desiredCapabilities'] = $testConfig->seleniumConfig->firefoxBinary;
        }

        $strategy = new PHPUnit_Extensions_Selenium2TestCase_SessionStrategy_Shared(new PHPUnit_Extensions_Selenium2TestCase_SessionStrategy_Isolated());
        self::$sharedSession = $strategy->session($parameters);
    }
}