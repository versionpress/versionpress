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

/** Useful for registering hooks in setup-hooks.php */
define('VERSIONPRESS_PLUGIN_FILE', __FILE__);

if (version_compare(PHP_VERSION, '5.6', '<')) {
    global $pagenow;

    if ($pagenow == 'plugins.php') {
        $phpVersionMessage = 'VersionPress requires PHP 5.6 or higher, your version is ' . phpversion() . '. ';
        $phpVersionMessage .= 'Please upgrade PHP or deactivate VersionPress.';

        add_action("after_plugin_row_versionpress/versionpress.php", 'vp_php_version_inline_error', 10, 2);
        function vp_php_version_inline_error($file, $plugin_data)
        {
            global $phpVersionMessage;

            $wp_list_table = _get_list_table('WP_Plugins_List_Table');
            echo '<tr class="active plugin-update-tr">';
            echo '<td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange">';
            echo '<div class="update-message">';
            echo $phpVersionMessage;
            echo '</div></td></tr>';
        }

        add_action('admin_notices', 'vp_php_version_admin_error_notice');
        function vp_php_version_admin_error_notice()
        {
            global $phpVersionMessage;

            $class = 'notice notice-error';
            $message = $phpVersionMessage;

            printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
        }
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
