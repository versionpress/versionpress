<?php

/**
 * Wrapper around test-config.ini
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

    function __construct($configFile) {
        $rawConfig = \Nette\Neon\Neon::decode(file_get_contents($configFile));

        $this->seleniumConfig = new SeleniumConfig();
        $this->seleniumConfig->firefoxBinary = $rawConfig['selenium']['firefox-binary'];
        $this->seleniumConfig->postCommitWaitTime = $rawConfig['selenium']['post-commit-wait-time'];

        $this->sites = array();
        foreach ($rawConfig['sites'] as $siteId => $rawSiteConfig) {
            $rawSiteConfig = array_merge_recursive($rawConfig['common-site-config'], $rawSiteConfig);

            $this->sites[$siteId] = new SiteConfig();

            // Site type
            $this->sites[$siteId]->isVagrant = $rawSiteConfig['type'] == "vagrant";

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
            $this->sites[$siteId]->vpConfig = isset($rawSiteConfig['vp-config']) ? $rawSiteConfig['vp-config'] : array();

        }

        $this->testSite = $this->sites[$rawConfig['test-site']];


    }

}
