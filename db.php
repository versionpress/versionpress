<?php
define('VERSIONPRESS_PLUGIN_DIR', dirname(__FILE__) . '/plugins/versionpress');
define('VERSIONPRESS_MIRRORING_DIR', VERSIONPRESS_PLUGIN_DIR . '/db');

require_once(VERSIONPRESS_PLUGIN_DIR . '/libs/NDebugger.php');
NDebugger::enable(NDebugger::DEVELOPMENT, VERSIONPRESS_PLUGIN_DIR . '/log');

if(file_exists(VERSIONPRESS_PLUGIN_DIR . '/.active'))
    require_once(VERSIONPRESS_PLUGIN_DIR . '/init.php');
