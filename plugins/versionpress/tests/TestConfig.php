<?php

class TestConfig {

    private $webDriver;
    private $firefoxExecutable;
    private $wordpressUrl;
    private $wordpressClearInstallationsPath;
    private $wordpressPath;
    private $dbHost;
    private $dbUser;
    private $dbPassword;
    private $dbName;
    private $testWpVersion;
    private $siteTitle;
    private $adminName;
    private $adminEmail;
    private $adminPassword;

    function __construct(array $rawConfig) {
        $this->wordpressUrl = $rawConfig['wordpress-url'];
        $this->wordpressClearInstallationsPath = $rawConfig['wordpress-clear-installations'];
        $this->wordpressPath = $rawConfig['wordpress-path'];
        $this->dbHost = $rawConfig['db-host'];
        $this->dbUser = $rawConfig['db-user'];
        $this->dbPassword = $rawConfig['db-pass'];
        $this->dbName = $rawConfig['db-name'];
        $this->testWpVersion = $rawConfig['test-wp-version'];
        $this->siteTitle = $rawConfig['site-title'];
        $this->adminName = $rawConfig['admin-name'];
        $this->adminEmail = $rawConfig['admin-email'];
        $this->adminPassword = $rawConfig['admin-pass'];

        $this->webDriver = $rawConfig['web-driver'] ?: "firefox";
        $this->firefoxExecutable = $rawConfig['firefox-executable'];
    }

    /**
     * @return string (default "firefox")
     */
    public function getWebDriver() {
        return $this->webDriver;
    }

    /**
     * @return string (default "" in which case the installed Firefox should be used)
     */
    public function getFirefoxExecutable() {
        return $this->firefoxExecutable;
    }

    /**
     * @return string
     */
    public function getDbHost() {
        return $this->dbHost;
    }

    /**
     * @return string
     */
    public function getDbName() {
        return $this->dbName;
    }

    /**
     * @return string
     */
    public function getDbPassword() {
        return $this->dbPassword;
    }

    /**
     * @return string
     */
    public function getDbUser() {
        return $this->dbUser;
    }

    /**
     * @return string
     */
    public function getTestWpVersion() {
        return $this->testWpVersion;
    }

    /**
     * @return string
     */
    public function getWordpressClearInstallationsPath() {
        return $this->wordpressClearInstallationsPath;
    }

    /**
     * @return string
     */
    public function getWordpressPath() {
        return $this->wordpressPath;
    }

    /**
     * @return string
     */
    public function getWordpressUrl() {
        return $this->wordpressUrl;
    }

    /**
     * @return string
     */
    public function getAdminEmail() {
        return $this->adminEmail;
    }

    /**
     * @return string
     */
    public function getAdminName() {
        return $this->adminName;
    }

    /**
     * @return string
     */
    public function getAdminPassword() {
        return $this->adminPassword;
    }

    /**
     * @return string
     */
    public function getSiteTitle() {
        return $this->siteTitle;
    }


}

