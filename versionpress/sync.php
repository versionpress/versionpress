<?php

require_once(dirname(__FILE__) . '/../../wp-load.php');
require_once(dirname(__FILE__) . '/PostSynchronizer.php');
require_once(dirname(__FILE__) . '/Git.php');

$storageFactory = new EntityStorageFactory(VERSIONPRESS_MIRRORING_DIR);
$postStorage = $storageFactory->getStorage('posts');

global $wpdb, $table_prefix;
$postSynchronizer = new PostSynchronizer($postStorage, $wpdb, $table_prefix . 'posts');
$postSynchronizer->syncPosts();

Git::commit('Test commit', dirname(__FILE__) . '/db');