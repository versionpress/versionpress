<?php
/*
Plugin Name: VersionPress
Plugin URI: http://versionpress.net/
Description: Git-versioning plugin for WordPress
Version: DEV
Author: VersionPress
Author URI: http://versionpress.net/
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.txt
*/

defined('ABSPATH') or die("Direct access not allowed");

if (version_compare(PHP_VERSION, '5.6', '<')) {
    register_activation_hook(__FILE__, 'vp_trigger_old_php_error');

    function vp_trigger_old_php_error()
    {
        wp_die('<h1>VersionPress could not be activated</h1>
            <p>
                You are using an unsupported version of PHP. We recommend using one of the
                <a href="http://php.net/supported-versions.php">actively supported</a>.
            </p>');
    }

    return;
}

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    register_activation_hook(__FILE__, 'vp_disable_plugin_activation');

    function vp_disable_plugin_activation()
    {
        wp_die('<h1>VersionPress could not be activated</h1>
            <p>
                It seems that your copy of VersionPress was not built correctly.
                Please download <a href="https://github.com/versionpress/versionpress/releases">release ZIP file
                from GitHub</a> and <a href="' . get_admin_url() . 'plugin-install.php?tab=upload">install it again</a>.
            </p>');
    }

    return;
}

require_once(__DIR__ . '/bootstrap.php');
require_once(__DIR__ . '/setup-hooks.php');
