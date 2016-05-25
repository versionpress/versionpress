<?php

namespace VersionPress\Tests\Selenium;

use VersionPress\Tests\Automation\WpAutomation;
use VersionPress\Tests\End2End\Utils\HttpStatusCodeUtil;
use VersionPress\Tests\Utils\TestConfig;
use VersionPress\Utils\PathUtils;

class SecurityTest extends \PHPUnit_Framework_TestCase
{
    /** @var TestConfig */
    private static $testConfig;
    /** @var WpAutomation */
    private static $wpAutomation;

    public function __construct()
    {
        self::$testConfig = TestConfig::createDefaultConfig();
        self::$wpAutomation = new WpAutomation(self::$testConfig->testSite, self::$testConfig->wpCliVersion);
    }

    /**
     * @test
     */
    public function gitRepositoryDoesntAllowDirectAccess()
    {
        $url = self::$testConfig->testSite->url . "/.git/config";
        $statusCode = HttpStatusCodeUtil::getStatusCode($url);
        $this->assertTrue($statusCode === 403 || $statusCode === 404, "Wrong HTTP status code ($statusCode)");
    }

    /**
     * @test
     */
    public function vpdbDoesntAllowDirectAccess()
    {
        $vpdbDir = self::$wpAutomation->getVpdbDir();
        $relativePathToVpdb = PathUtils::getRelativePath(self::$wpAutomation->getWebRoot(), $vpdbDir);
        $url = self::$testConfig->testSite->url . '/' . $relativePathToVpdb . "/web.config";
        $statusCode = HttpStatusCodeUtil::getStatusCode($url);
        $this->assertTrue($statusCode === 403 || $statusCode === 404, "Wrong HTTP status code ($statusCode)");
    }

    /**
     * @test
     */
    public function vpconfigDoesntAllowDirectAccess()
    {
        $pluginsDir = self::$wpAutomation->getPluginsDir();
        $relativePathToPluginsDir = PathUtils::getRelativePath(self::$wpAutomation->getWebRoot(), $pluginsDir);

        $url = self::$testConfig->testSite->url . '/' . $relativePathToPluginsDir . "/versionpress/vpconfig.yml";
        $statusCode = HttpStatusCodeUtil::getStatusCode($url);
        $this->assertTrue($statusCode === 403 || $statusCode === 404, "Wrong HTTP status code ($statusCode)");
    }
}
