<?php

namespace VersionPress\Tests\Utils;

/**
 * Represents a single testing site
 */
class SiteConfig {

    //----------------------
    // General settings
    //----------------------

    /**
     * Site name - its associative key in the test-config array
     * 
     * @var string
     */
    public $name;

    /**
     * Whether the site runs locally or in a Vagrant machine. Is detected from the $host
     * property - it is false if host is "localhost", true otherwise.
     *
     * @var bool
     */
    public $isVagrant;

    /**
     * Host where the machine runs. "localhost" for local sites, anything else assumes a Vagrant site.
     *
     * @var string
     */
    public $host;


    //----------------------
    // DB config
    //----------------------


    /**
     * DB host as should be configured in wp-config.php. Note that this may be "localhost" even
     * if the machine itself runs on a different (Vagrant) host, so it is quite normal that
     * $host is something like "vagrant.local" and $dbHost is "localhost" (local for that machine).
     *
     * @var string
     */
    public $dbHost;

    /**
     * @var string
     */
    public $dbName;

    /**
     * @var string
     */
    public $dbUser;

    /**
     * @var string
     */
    public $dbPassword;

    /**
     * @var string
     */
    public $dbTablePrefix;


    //----------------------
    // WP site config
    //----------------------


    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    public $url;

    /**
     * Site title
     *
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $adminName;

    /**
     * @var string
     */
    public $adminEmail;

    /**
     * @var string
     */
    public $adminPassword;

    /**
     * @var string
     */
    public $wpVersion;

    /**
     * @var string
     */
    public $wpLocale;

    /**
     * @var bool
     */
    public $wpAutoupdate;

    //----------------------
    // VersionPress config
    //----------------------

    /**
     * Array of 'config-option' (string) => 'config-value' (string)
     *
     * @var array
     */
    public $vpConfig;
}
