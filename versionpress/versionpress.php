<?php

require_once(dirname(__FILE__) . '/EntityStorage.php');
require_once(dirname(__FILE__) . '/EntityStorageFactory.php');
require_once(dirname(__FILE__) . '/Mirror.php');
require_once(dirname(__FILE__) . '/MirroringDatabase.php');
require_once(dirname(__FILE__) . '/PostStorage.php');

global $wpdb, $table_prefix;
$wpdb = new MirroringDatabase(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST, new Mirror(new EntityStorageFactory(VERSIONPRESS_MIRRORING_DIR, $table_prefix)));