<?php

/**
 * Uninstallation script for VersionPress. The VP_KEEP_REPO constant must be defined
 * before execution - this script then either keeps or removes the `.git` repository.
 */

defined('WP_UNINSTALL_PLUGIN') or die('Direct access not allowed');

unlink(WP_CONTENT_DIR . '/db.php');

global $wp_filesystem;

if (!VP_KEEP_REPO) {

    $url = wp_nonce_url('plugins.php');
    if (false === ($creds = request_filesystem_credentials($url, '', false, false, null) ) ) {
        echo "Could not create filesystem credentials";
        return;
    }

    if ( ! WP_Filesystem($creds) ) {
        request_filesystem_credentials($url, '', true, false, null);
        echo "Filesystem credentials were not available";
        return;
    }

    $wp_filesystem->rmdir(ABSPATH . '.git', true);
}



global $wpdb;

$table_prefix = $wpdb->prefix;

$queries[] = "DROP VIEW IF EXISTS `{$table_prefix}vp_reference_details`";
$queries[] = "DROP TABLE IF EXISTS `{$table_prefix}vp_references`";
$queries[] = "DROP TABLE IF EXISTS `{$table_prefix}vp_id`";

foreach ($queries as $query) {
    $wpdb->query($query);
}
