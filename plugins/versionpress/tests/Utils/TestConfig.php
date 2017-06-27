<?php

namespace VersionPress\Tests\Utils;

use Symfony\Component\Yaml\Yaml;

/**
 * Test config, loaded from test-config.yml
 */
class TestConfig
{

    /**
     * @var SeleniumConfig
     */
    public $seleniumConfig;

    /**
     * Associative array of configured sites where key is the site name and value is a SiteConfig.
     *
     * @var SiteConfig[]
     */
    public $sites;

    /**
     * Site used for current testing. One of the `$sites`.
     *
     * @var SiteConfig
     */
    public $testSite;

    /**
     * Type of workers that will run the tests (selenium / wp-cli).
     *
     * @var string
     */
    public $end2endTestType;

    /**
     * Version of WP-CLI that will be used (e.g. 0.19.0 / latest-stable)
     *
     * @var string
     */
    public $wpCliVersion;

    public static $defaultConfigFile;

    public function __construct($configFile)
    {
        $rawConfig = Yaml::parse(file_get_contents($configFile));

        // General configuration
        $this->end2endTestType = $rawConfig['end2end-test-type'];

        // Selenium settings
        $this->seleniumConfig = new SeleniumConfig();
        $this->seleniumConfig->host = $rawConfig['selenium']['host'];
        $this->seleniumConfig->postCommitWaitTime = $rawConfig['selenium']['post-commit-wait-time'];

        // WP-CLI settings
        $this->wpCliVersion = $rawConfig['wp-cli-version'];

        $this->sites = [];
        foreach ($rawConfig['sites'] as $siteId => $rawSiteConfig) {
            $rawSiteConfig = array_merge_recursive($rawConfig['common-site-config'], $rawSiteConfig);

            $this->sites[$siteId] = new SiteConfig();

            // General settings
            $this->sites[$siteId]->name = $siteId;
            $this->sites[$siteId]->host = $rawSiteConfig['host'];
            $this->sites[$siteId]->installationType = $rawSiteConfig['installation-type'];

            // DB config
            $this->sites[$siteId]->dbHost = $rawSiteConfig['db']['host'];
            $this->sites[$siteId]->dbName = $rawSiteConfig['db']['dbname'];
            $this->sites[$siteId]->dbUser = $rawSiteConfig['db']['user'];
            $this->sites[$siteId]->dbPassword = $rawSiteConfig['db']['password'];
            $this->sites[$siteId]->dbTablePrefix = $rawSiteConfig['db']['table-prefix'];

            // WP site config
            $this->sites[$siteId]->path = $rawSiteConfig['wp-site']['path'];
            $this->sites[$siteId]->url = $rawSiteConfig['wp-site']['url'];
            $this->sites[$siteId]->wpAdminPath = $rawSiteConfig['wp-site']['wp-admin-path'];
            $this->sites[$siteId]->title = $rawSiteConfig['wp-site']['title'];
            $this->sites[$siteId]->adminUser = $rawSiteConfig['wp-site']['admin-user'];
            $this->sites[$siteId]->adminPassword = $rawSiteConfig['wp-site']['admin-pass'];
            $this->sites[$siteId]->adminEmail = $rawSiteConfig['wp-site']['admin-email'];
            $this->sites[$siteId]->wpVersion = $rawSiteConfig['wp-site']['wp-version'];
            $this->sites[$siteId]->wpLocale = isset($rawSiteConfig['wp-site']['wp-locale'])
                ? $rawSiteConfig['wp-site']['wp-locale'] : null;
            $this->sites[$siteId]->wpAutoupdate = $rawSiteConfig['wp-site']['wp-autoupdate'];
        }

        $this->testSite = $this->sites[$rawConfig['test-site']];
    }

    /**
     * Creates new instance of TestConfig from {@link TestConfig::$defaultConfigFile}.
     *
     * @return TestConfig
     */
    public static function createDefaultConfig()
    {
        return new self(self::$defaultConfigFile);
    }
}
