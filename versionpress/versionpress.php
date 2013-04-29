<?php

require_once(dirname(__FILE__) . '/EntityStorage.php');
require_once(dirname(__FILE__) . '/EntityStorageFactory.php');
require_once(dirname(__FILE__) . '/Mirror.php');
require_once(dirname(__FILE__) . '/MirroringDatabase.php');
require_once(dirname(__FILE__) . '/PostStorage.php');
require_once(dirname(__FILE__) . '/IniSerializer.php');
require_once(dirname(__FILE__) . '/Git.php');

global $wpdb, $table_prefix;
$mirror = new Mirror(new EntityStorageFactory(VERSIONPRESS_MIRRORING_DIR));
$wpdb = new MirroringDatabase(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST, $mirror);


register_shutdown_function(function () use ($mirror) {
    if($mirror->wasAffected()) {
        Git::commit('Commit', dirname(__FILE__) . '/db');
    }
});