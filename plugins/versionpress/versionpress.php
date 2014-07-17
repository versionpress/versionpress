<?php
/*
Plugin Name: VersionPress
Plugin URI: http://versionpress.net/
Description: Git-versioning plugin for WordPress
Author: Agilio
Version: 1.0
*/
function versionpress_activate() {
    copy(dirname(__FILE__) . '/_db.php', WP_CONTENT_DIR . '/db.php');
}

function versionpress_deactivate() {
    global $wpdb, $table_prefix;
    unlink(VERSIONPRESS_PLUGIN_DIR . '/.active');
    unlink(WP_CONTENT_DIR . '/db.php');

    $queries[] = 'DROP VIEW `' . $table_prefix . 'vp_reference_details`;';
    $queries[] = 'DROP TABLE `' . $table_prefix . 'vp_references`, `' . $table_prefix . 'vp_id`;';

    foreach ($queries as $query) {
        $wpdb->query($query);
    }
}

register_activation_hook(__FILE__, 'versionpress_activate');
register_deactivation_hook(__FILE__, 'versionpress_deactivate');

function isActive() {
    return defined('VERSIONPRESS_PLUGIN_DIR') && file_exists(VERSIONPRESS_PLUGIN_DIR . '/.active');
}

add_action( 'admin_menu', 'register_versionpress_menu' );

function register_versionpress_menu(){
    add_menu_page(
        'VersionPress',
        'VersionPress',
        'manage_options',
        'versionpress/administration/versionpress.php',
        '',
        null,
        0.001234987
    );

    if(isActive())
        add_submenu_page(
            'versionpress/administration/versionpress.php',
            'Synchronization',
            'Synchronization',
            'manage_options',
            'versionpress/administration/sync.php'
        );
}