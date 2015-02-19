<?php

/**
 * Wrapper around test-config.ini
 */
class TestConfig {

    private $firefoxExecutable;
    private $siteUrl;
    private $cleanInstallationsPath;
    private $sitePath;
    private $dbHost;
    private $dbUser;
    private $dbPassword;
    private $dbName;
    private $dbPrefix;
    private $wpVersion;
    private $siteTitle;
    private $adminName;
    private $adminEmail;
    private $adminPassword;
    private $afterRedirectWaitingTime;
    private $isVagrant;

    function __construct(array $rawConfig) {

        // DB
        $this->dbHost = $rawConfig['db-host'];
        $this->dbUser = $rawConfig['db-user'];
        $this->dbPassword = $rawConfig['db-pass'];
        $this->dbName = $rawConfig['db-name'];
        $this->dbPrefix = $rawConfig['db-prefix'];

        // Site settings
        $this->siteUrl = $rawConfig['site-url'];
        $this->sitePath = $rawConfig['site-path'];
        $this->siteTitle = $rawConfig['site-title'];
        $this->adminName = $rawConfig['admin-name'];
        $this->adminEmail = $rawConfig['admin-email'];
        $this->adminPassword = $rawConfig['admin-pass'];
        $this->isVagrant = isset($rawConfig['is-vagrant']) ? $rawConfig['is-vagrant'] == "1" : false;

        // Automation
        $this->cleanInstallationsPath = $rawConfig['wp-clean-installations'];
        $this->firefoxExecutable = $rawConfig['selenium-firefox-executable'];
        $this->wpVersion = $rawConfig['wp-version'];
        $this->afterRedirectWaitingTime = $rawConfig['after-redirect-waiting-time'];
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
    public function getDbPrefix() {
        return $this->dbPrefix;
    }

    /**
     * @return string
     */
    public function getWpVersion() {
        return $this->wpVersion;
    }

    /**
     * @return string
     */
    public function getCleanInstallationsPath() {
        return $this->cleanInstallationsPath;
    }

    /**
     * @return string
     */
    public function getSitePath() {
        return $this->sitePath;
    }

    /**
     * @return string
     */
    public function getSiteUrl() {
        return $this->siteUrl;
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

    /**
     * @return int Time in ms
     */
    public function getAfterRedirectWaitingTime() {
        return $this->afterRedirectWaitingTime;
    }

    /**
     * @return bool
     */
    public function getIsVagrant() {
        return $this->isVagrant;
    }
}
