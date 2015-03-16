<?php

namespace VersionPress\Tests\Utils;

use Nette\Neon\Neon;

/**
 * Test config, loaded from test-config.neon
 */
class TestConfig {

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

    public static $defaultConfigFile;

    function __construct($configFile) {
        $rawConfig = Neon::decode(file_get_contents($configFile));

        $this->seleniumConfig = new SeleniumConfig();
        $this->seleniumConfig->firefoxBinary = $rawConfig['selenium']['firefox-binary'];
        $this->seleniumConfig->postCommitWaitTime = $rawConfig['selenium']['post-commit-wait-time'];
        $this->end2endTestType = $rawConfig['end2end-test-type'];

        $this->sites = array();
        foreach ($rawConfig['sites'] as $siteId => $rawSiteConfig) {
            $rawSiteConfig = array_merge_recursive($rawConfig['common-site-config'], $rawSiteConfig);

            $this->sites[$siteId] = new SiteConfig();

            // General settings
            $this->sites[$siteId]->name = $siteId;
            $this->sites[$siteId]->host = $rawSiteConfig['host'];
            $this->sites[$siteId]->isVagrant = $rawSiteConfig['host'] != "localhost";

            // DB config
            $this->sites[$siteId]->dbHost = $rawSiteConfig['db']['host'];
            $this->sites[$siteId]->dbName = $rawSiteConfig['db']['dbname'];
            $this->sites[$siteId]->dbUser = $rawSiteConfig['db']['user'];
            $this->sites[$siteId]->dbPassword = $rawSiteConfig['db']['password'];
            $this->sites[$siteId]->dbTablePrefix = $rawSiteConfig['db']['table-prefix'];

            // WP site config
            $this->sites[$siteId]->path = $rawSiteConfig['wp-site']['path'];
            $this->sites[$siteId]->url = $rawSiteConfig['wp-site']['url'];
            $this->sites[$siteId]->title = $rawSiteConfig['wp-site']['title'];
            $this->sites[$siteId]->adminName = $rawSiteConfig['wp-site']['admin-name'];
            $this->sites[$siteId]->adminPassword = $rawSiteConfig['wp-site']['admin-pass'];
            $this->sites[$siteId]->adminEmail = $rawSiteConfig['wp-site']['admin-email'];
            $this->sites[$siteId]->wpVersion = $rawSiteConfig['wp-site']['wp-version'];

            // VP config
            $this->sites[$siteId]->vpConfig = $rawSiteConfig['vp-config'];

            // If the site overrode a common config vp-config value, array_merge_recursive() caused that the key now
            // contains array with two items, first being the empty value from common-site-config
            // and the other one being the real one. We want just the real one.
            foreach ($this->sites[$siteId]->vpConfig as $key => $value) {
                if (is_array($value)) {
                    $this->sites[$siteId]->vpConfig[$key] = $value[1];
                }
            }

        }

        $this->testSite = $this->sites[$rawConfig['test-site']];
    }

    /**
     * Creates new instance of TestConfig from {@link TestConfig::$defaultConfigFile}.
     *
     * @return TestConfig
     */
    public static function createDefaultConfig() {
        return new self(self::$defaultConfigFile);
    }

}
