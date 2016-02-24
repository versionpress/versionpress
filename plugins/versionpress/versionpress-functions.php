<?php
// Global VersionPress functions. Stored here and included from bootstrap.php because
// versionpress.php might not be always loaded (e.g., in WP-CLI commands).

use VersionPress\Utils\FileSystem;

function vp_flush_regenerable_options() {
    wp_cache_flush();
    $taxonomies = get_taxonomies();
    foreach($taxonomies as $taxonomy) {
        delete_option("{$taxonomy}_children");
        // Regenerate {$taxonomy}_children
        _get_term_hierarchy($taxonomy);
    }
}

function vp_enable_maintenance() {
    $maintenance_string = '<?php define("VP_MAINTENANCE", true); $upgrading = ' . time() . '; ?>';
    file_put_contents(ABSPATH . '.maintenance', $maintenance_string);
}

function vp_disable_maintenance() {
    FileSystem::remove(ABSPATH . '.maintenance');
}

function vp_verify_nonce($name) {
    if (!wp_verify_nonce(@$_REQUEST['_wpnonce'], $name)) {
        wp_die(
            '<h1>' . __('Cheatin&#8217; uh?') . '</h1>' .
            '<p>' . __('Or maybe it\'s just a long time since you opened previous page. In this case please try it again.') . '</p>',
            403
        );
    }
}
