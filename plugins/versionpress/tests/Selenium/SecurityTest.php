<?php

namespace VersionPress\Tests\Selenium;

use VersionPress\Tests\End2End\Utils\HttpStatusCodeUtil;
use VersionPress\Tests\Utils\TestConfig;

class SecurityTest extends \PHPUnit_Framework_TestCase {

    private static $testConfig;

    public function __construct() {
        self::$testConfig = TestConfig::createDefaultConfig();
    }

    /**
     * @test
     */
    public function gitRepositoryDoesntAllowDirectAccess() {
        $url = self::$testConfig->testSite->url . "/.git/config";
        $statusCode = HttpStatusCodeUtil::getStatusCode($url);
        $this->assertEquals(403, $statusCode, "Wrong HTTP status codes");
    }

    /**
     * @test
     */
    public function vpdbDoesntAllowDirectAccess() {
        $url = self::$testConfig->testSite->url . "/wp-content/vpdb/terms.ini";
        $statusCode = HttpStatusCodeUtil::getStatusCode($url);
        $this->assertEquals(403, $statusCode, "Wrong HTTP status codes");
    }

    /**
     * @test
     */
    public function vpconfigDoesntAllowDirectAccess() {
        $url = self::$testConfig->testSite->url . "/wp-content/plugins/versionpress/vpconfig.neon";
        $statusCode = HttpStatusCodeUtil::getStatusCode($url);
        $this->assertEquals(403, $statusCode, "Wrong HTTP status codes");
    }

}
