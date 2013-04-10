<?php

require_once(dirname(__FILE__) . '/MirroringDatabase.php');

global $wpdb;
$wpdb = new MirroringDatabase(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST, DB_MIRRORING_DIR);