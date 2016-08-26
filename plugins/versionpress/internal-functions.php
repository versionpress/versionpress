<?php
// Global VersionPress functions. Stored here and included from bootstrap.php because
// versionpress.php might not be always loaded (e.g., in WP-CLI commands).

use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\WpdbMirrorBridge;
use VersionPress\DI\VersionPressServices;
use VersionPress\Git\Committer;
use VersionPress\Storages\StorageFactory;
use VersionPress\Utils\FileSystem;
use VersionPress\Utils\QueryLanguageUtils;

function vp_flush_regenerable_options()
{
    wp_cache_flush();
    $taxonomies = get_taxonomies();
    foreach ($taxonomies as $taxonomy) {
        delete_option("{$taxonomy}_children");
        // Regenerate {$taxonomy}_children
        _get_term_hierarchy($taxonomy);
    }
}

function vp_commit_all_frequently_written_entities()
{
    global $versionPressContainer;

    /**
     * @var DbSchemaInfo $dbSchemaInfo
     * @var Committer $committer
     */
    $dbSchemaInfo = $versionPressContainer->resolve(VersionPressServices::DB_SCHEMA);
    $committer = $versionPressContainer->resolve(VersionPressServices::COMMITTER);

    $rules = $dbSchemaInfo->getRulesForFrequentlyWrittenEntities();

    vp_save_frequently_written_entities($rules);

    $committer->commit();

}

function vp_save_frequently_written_entities($rules)
{
    global $versionPressContainer;

    /**
     * @var DbSchemaInfo $dbSchemaInfo
     * @var Database $database
     * @var WpdbMirrorBridge $wpdbMirrorBridge
     * @var StorageFactory $storageFactory
     */
    $dbSchemaInfo = $versionPressContainer->resolve(VersionPressServices::DB_SCHEMA);
    $database = $versionPressContainer->resolve(VersionPressServices::DATABASE);
    $wpdbMirrorBridge = $versionPressContainer->resolve(VersionPressServices::WPDB_MIRROR_BRIDGE);
    $storageFactory = $versionPressContainer->resolve(VersionPressServices::STORAGE_FACTORY);

    foreach ($rules as $entityName => $rulesWithInterval) {
        $storageFactory->getStorage($entityName)->ignoreFrequentlyWrittenEntities = false;

        $table = $dbSchemaInfo->getPrefixedTableName($entityName);

        foreach ($rulesWithInterval as $ruleAndInterval) {
            $restriction = QueryLanguageUtils::createSqlRestrictionFromRule($ruleAndInterval['rule']);
            $sql = "SELECT * FROM $table WHERE $restriction";

            $results = $database->get_results($sql, ARRAY_A);
            foreach ($results as $data) {
                $wpdbMirrorBridge->update($table, $data, $data);
            }
        }
    }
}

function vp_enable_maintenance()
{
    $maintenance_string = '<?php define("VP_MAINTENANCE", true); $upgrading = ' . time() . '; ?>';
    file_put_contents(ABSPATH . '.maintenance', $maintenance_string);
}

function vp_disable_maintenance()
{
    FileSystem::remove(ABSPATH . '.maintenance');
}

function vp_is_ajax()
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
}

function vp_verify_nonce($name)
{
    if (!wp_verify_nonce(@$_REQUEST['_wpnonce'], $name)) {
        wp_die(
            '<h1>' . __('Cheatin&#8217; uh?') . '</h1>' .
            '<p>' . __('Or maybe it\'s just a long time since you opened previous page. In this case please try it again.') . '</p>', // @codingStandardsIgnoreLine
            403
        );
    }
}

function vp_check_permissions()
{
    if (!current_user_can('manage_options')) {
        wp_die(
            '<h1>' . __('Cheatin&#8217; uh?') . '</h1>' .
            '<p>' . __('You are not allowed to access VersionPress.') . '</p>',
            403
        );
    }
}

/**
 * Multiple methods of disabling output buffering.
 * @see http://www.binarytides.com/php-output-content-browser-realtime-buffering/
 */
function vp_disable_output_buffering()
{
    // Turn off output buffering
    ini_set('output_buffering', 'off');
    // Turn off PHP output compression
    ini_set('zlib.output_compression', false);

    // Flush (send) the output buffer and turn off output buffering
    /** @noinspection PhpUsageOfSilenceOperatorInspection */
    while (@ob_end_flush()) {
        ;
    }

    // Implicitly flush the buffer(s)
    ini_set('implicit_flush', true);
    ob_implicit_flush(true);

    //prevent apache from buffering it for deflate/gzip
    header("Content-type: text/plain");
    header('Cache-Control: no-cache'); // recommended to prevent caching of event data.

    for ($i = 0; $i < 1000; $i++) {
        echo ' ';
    }

    ob_flush();
    flush();
}

/**
 * @param Database $database
 */
function vp_fix_comments_count($database)
{
    $sql = "update {$database->prefix}posts set comment_count =
     (select count(*) from {$database->prefix}comments
      where comment_post_ID = {$database->prefix}posts.ID and comment_approved = 1
     );";

    $database->query($sql);
}

/**
* @param Database $database
*/
function vp_fix_posts_count($database)
{
    $sql = "update {$database->term_taxonomy} tt set tt.count =
          (select count(*) from {$database->term_relationships} tr where tr.term_taxonomy_id = tt.term_taxonomy_id);";
    $database->query($sql);
}
