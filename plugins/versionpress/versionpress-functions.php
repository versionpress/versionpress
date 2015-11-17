<?php
// Global VersionPress functions. Stored here and included from bootstrap.php because
// versionpress.php might not be always loaded (e.g., in WP-CLI commands).

function vp_flush_regenerable_options() {
    wp_cache_flush();
    $taxonomies = get_taxonomies();
    foreach($taxonomies as $taxonomy) {
        delete_option("{$taxonomy}_children");
        // Regenerate {$taxonomy}_children
        _get_term_hierarchy($taxonomy);
    }
}
