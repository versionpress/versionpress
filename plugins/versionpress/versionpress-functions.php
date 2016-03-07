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

function vp_is_ajax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
}

/**
 * Multiple methods of disabling output buffering.
 * @see http://www.binarytides.com/php-output-content-browser-realtime-buffering/
 */
function vp_disable_output_buffering() {
    // Turn off output buffering
    ini_set('output_buffering', 'off');
    // Turn off PHP output compression
    ini_set('zlib.output_compression', false);

    // Flush (send) the output buffer and turn off output buffering
    /** @noinspection PhpUsageOfSilenceOperatorInspection */
    while (@ob_end_flush()) ;

    // Implicitly flush the buffer(s)
    ini_set('implicit_flush', true);
    ob_implicit_flush(true);

    //prevent apache from buffering it for deflate/gzip
    header("Content-type: text/plain");
    header('Cache-Control: no-cache'); // recommended to prevent caching of event data.

    for ($i = 0; $i < 1000; $i++) echo ' ';

    ob_flush();
    flush();
}
