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
     * Whether the site runs locally or in a Vagrant machine
     *
     * @var bool
     */
    public $isVagrant;


    //----------------------
    // DB config
    //----------------------


    /**
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
