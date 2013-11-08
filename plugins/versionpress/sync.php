<?php
// Enable WP_DEBUG mode
define('WP_DEBUG', true);
// Enable Debug logging to the /wp-content/debug.log file
define('WP_DEBUG_LOG', true);

require_once(VERSIONPRESS_PLUGIN_DIR . '/../../../wp-load.php');
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

$synchronizationProcess = new SynchronizationProcess(new SynchronizerFactory($storageFactory, $wpdb, $schemaInfo));

$synchronizationQueue = ['options', 'users', 'usermeta', 'posts', 'comments', 'terms', 'term_taxonomy', 'term_relationships'];

$synchronizationProcess->synchronize($synchronizationQueue);
Git::commit('Synchronized');