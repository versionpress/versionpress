<?php

require_once(dirname(__FILE__) . '/../../wp-load.php');
require_once(dirname(__FILE__) . '/PostSynchronizer.php');
require_once(dirname(__FILE__) . '/OptionsSynchronizer.php');
require_once(dirname(__FILE__) . '/Git.php');

$storageFactory = new EntityStorageFactory(VERSIONPRESS_MIRRORING_DIR);
$postStorage = $storageFactory->getStorage('options');

global $wpdb, $table_prefix;
$postSynchronizer = new OptionsSynchronizer($postStorage, $wpdb, $table_prefix . 'options');
$postSynchronizer->synchronize();

//Git::commit('Test commit', dirname(__FILE__) . '/db');