<?php

namespace VersionPress\Tests\Workflow;

use PHPUnit_Framework_TestCase;
use VersionPress\Tests\Automation\WpAutomation;
use VersionPress\Tests\Utils\SiteConfig;
use VersionPress\Tests\Utils\TestConfig;

class TemporaryDbTest extends PHPUnit_Framework_TestCase
{

    /** @var TestConfig */
    private static $testConfig;

    /** @var SiteConfig */
    private static $siteConfig;

    /** @var SiteConfig */
    private static $cloneSiteConfig;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$testConfig = TestConfig::createDefaultConfig();
        self::$siteConfig = self::$testConfig->testSite;
    }

    /**
     * @test
     */
    public function canConnectToDb()
    {
        $siteConfig = self::$siteConfig;
        $wpAutomation = new WpAutomation($siteConfig, self::$testConfig->wpCliVersion);

        // This will error out if it's run too soon; the testing infrastructure should
        // implement some waiting mechanism to let MySQL fully start.
        $wpAutomation->runWpCliCommand('db', 'query',
        [
            'dbuser' => $siteConfig->dbUser,
            'dbpass' => $siteConfig->dbPassword,
            'SELECT User FROM mysql.user'
        ]);

        $this->assertTrue(true);
    }

}
