<?php

require_once(dirname(__FILE__) . '/../../wp-load.php');
require_once(dirname(__FILE__) . '/PostsAndCommentsBaseSynchronizer.php');
require_once(dirname(__FILE__) . '/CommentSynchronizer.php');
require_once(dirname(__FILE__) . '/OptionsSynchronizer.php');
require_once(dirname(__FILE__) . '/Git.php');

$storageFactory = new EntityStorageFactory(VERSIONPRESS_MIRRORING_DIR);
$postStorage = $storageFactory->getStorage('comments');

global $wpdb, $table_prefix;
$postSynchronizer = new CommentSynchronizer($postStorage, $wpdb, $table_prefix . 'comments');
$postSynchronizer->synchronize();

Git::commit('Test commit', dirname(__FILE__) . '/db');