<?php

namespace VersionPress\Tests\Utils;

/**
 * Represents a single testing site
 */
class SiteConfig
{

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
     * Host where the machine runs. "localhost" for local site.
     *
     * @var string
     */
    public $host;

    /**
     * Type of site - standard WP structure, Composer-based etc.
     * Possible values: standard / composer
     *
     * @var string
     */
    public $installationType = 'standard';


    //----------------------
    // DB config
    //----------------------


    /**
     * DB host as should be configured in wp-config.php. Note that this may be "localhost" even
     * if the machine itself runs on a different host, so it is quite normal that
     * $host is something like "machine.local" and $dbHost is "localhost" (local for that machine).
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
}
