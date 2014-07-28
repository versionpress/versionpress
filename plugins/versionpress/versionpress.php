<?php
/*
Plugin Name: VersionPress
Plugin URI: http://versionpress.net/
Description: Git-versioning plugin for WordPress
Author: Agilio
Version: 1.0
*/

defined('ABSPATH') or die("Direct access not allowed");


register_activation_hook(__FILE__, 'versionpress_activate');
register_deactivation_hook(__FILE__, 'versionpress_deactivate');
register_uninstall_hook(__FILE__, 'versionpress_uninstall');


function versionpress_activate() {
    copy(dirname(__FILE__) . '/_db.php', WP_CONTENT_DIR . '/db.php');
}

function versionpress_deactivate() {

    unlink(VERSIONPRESS_PLUGIN_DIR . '/.active');
    unlink(WP_CONTENT_DIR . '/db.php');
}

function versionpress_uninstall() {

    global $wpdb;

    $queries[] = 'DROP VIEW `' . $wpdb->prefix . 'vp_reference_details`;';
    $queries[] = 'DROP TABLE `' . $wpdb->prefix . 'vp_references`, `' . $wpdb->prefix . 'vp_id`;';

    foreach ($queries as $query) {
        $wpdb->query($query);
    }

}


function isActive() {
    return defined('VERSIONPRESS_PLUGIN_DIR') && file_exists(VERSIONPRESS_PLUGIN_DIR . '/.active');
}

add_action( 'admin_menu', 'register_versionpress_menu' );

function register_versionpress_menu(){
    add_menu_page(
        'VersionPress',
        'VersionPress',
        'manage_options',
        'versionpress/administration/index.php',
        '',
        null,
        0.001234987
    );

    if(isActive())
        add_submenu_page(
            'versionpress/administration/index.php',
            'Synchronization',
            'Synchronization',
            'manage_options',
            'versionpress/administration/sync.php'
        );
}