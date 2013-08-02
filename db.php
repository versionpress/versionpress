<?php
define('VERSIONPRESS_DIR', dirname(__FILE__) . '/versionpress');
define('VERSIONPRESS_MIRRORING_DIR', VERSIONPRESS_DIR . '/db');

if(file_exists(VERSIONPRESS_DIR . '/.active'))
    require_once(dirname(__FILE__) . '/versionpress/versionpress.php');
