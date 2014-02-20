<?php
// Enable WP_DEBUG mode
define('WP_DEBUG', true);
// Enable Debug logging to the /wp-content/debug.log file
define('WP_DEBUG_LOG', true);

require_once(dirname(__FILE__) . '/../../../wp-load.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/synchronizers/Synchronizer.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/synchronizers/SynchronizerBase.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/synchronizers/OptionsSynchronizer.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/synchronizers/PostsSynchronizer.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/synchronizers/CommentsSynchronizer.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/synchronizers/UsersSynchronizer.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/synchronizers/UserMetaSynchronizer.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/synchronizers/TermsSynchronizer.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/synchronizers/TermTaxonomySynchronizer.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/synchronizers/TermRelationshipsSynchronizer.php');

require_once(VERSIONPRESS_PLUGIN_DIR . '/src/synchronizers/SynchronizerFactory.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/synchronizers/SynchronizationProcess.php');


global $wpdb, $table_prefix, $storageFactory, $schemaInfo;
$wpdb->show_errors();

function fixCommentCount(wpdb $wpdb) {
    $sql = "update {$wpdb->prefix}posts set comment_count =
     (select count(*) from {$wpdb->prefix}comments where comment_post_ID = {$wpdb->prefix}posts.ID and comment_approved = 1);";
    $wpdb->query($sql);
}

$synchronizationProcess = new SynchronizationProcess(new SynchronizerFactory($storageFactory, $wpdb, $schemaInfo));

$synchronizationQueue = ['options', 'users', 'usermeta', 'posts', 'comments', 'terms', 'term_taxonomy', 'term_relationships'];
$synchronizationProcess->synchronize($synchronizationQueue);
fixCommentCount($wpdb);
Git::commit('Synchronized');