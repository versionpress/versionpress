<?php
/*
Plugin Name: VersionPress
Author: Agilio
Version: 0.0.1-alfa
*/
function versionpress_activate() {
    touch(VERSIONPRESS_DIR . '/.active');
    set_time_limit(0);
    require_once(VERSIONPRESS_DIR . '/install.php');
}

function versionpress_deactivate() {
    global $wpdb, $table_prefix;
    unlink(VERSIONPRESS_DIR . '/.active');

    $queries[] = 'DROP VIEW `' . $table_prefix . 'vp_reference_details`;';
    $queries[] = 'DROP TABLE `' . $table_prefix . 'vp_references`, `' . $table_prefix . 'vp_id`;';

    foreach ($queries as $query) {
        $wpdb->query($query);
    }
}

register_activation_hook(__FILE__, 'versionpress_activate');
register_deactivation_hook(__FILE__, 'versionpress_deactivate');