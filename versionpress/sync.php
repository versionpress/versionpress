<?php
// Enable WP_DEBUG mode
define('WP_DEBUG', true);
// Enable Debug logging to the /wp-content/debug.log file
define('WP_DEBUG_LOG', true);

require_once(dirname(__FILE__) . '/../../wp-load.php');
require_once(dirname(__FILE__) . '/synchronizers/Synchronizer.php');
require_once(dirname(__FILE__) . '/synchronizers/SynchronizerBase.php');
require_once(dirname(__FILE__) . '/synchronizers/OptionsSynchronizer.php');
require_once(dirname(__FILE__) . '/synchronizers/PostsSynchronizer.php');
require_once(dirname(__FILE__) . '/synchronizers/CommentsSynchronizer.php');
require_once(dirname(__FILE__) . '/synchronizers/UsersSynchronizer.php');
require_once(dirname(__FILE__) . '/synchronizers/UserMetaSynchronizer.php');
require_once(dirname(__FILE__) . '/synchronizers/TermsSynchronizer.php');

require_once(dirname(__FILE__) . '/synchronizers/SynchronizerFactory.php');
require_once(dirname(__FILE__) . '/synchronizers/SynchronizationProcess.php');

global $wpdb, $table_prefix, $storageFactory, $schemaInfo;
$wpdb->show_errors();

$synchronizationProcess = new SynchronizationProcess(new SynchronizerFactory($storageFactory, $wpdb, $schemaInfo));
$synchronizationProcess->synchronize('options', 'posts', 'comments', 'users', 'usermeta', 'terms');
