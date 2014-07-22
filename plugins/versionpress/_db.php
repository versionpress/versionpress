<?php
define('VERSIONPRESS_PLUGIN_DIR', dirname(__FILE__) . '/plugins/versionpress');
define('VERSIONPRESS_MIRRORING_DIR', VERSIONPRESS_PLUGIN_DIR . '/db');

require_once(VERSIONPRESS_PLUGIN_DIR . '/libs/nette.min.php');

$robotLoader = new NRobotLoader();
$robotLoader->addDirectory(dirname(__FILE__) . '/src');
$robotLoader->setCacheStorage(new NFileStorage(dirname(__FILE__) . '/temp'));
$robotLoader->register();

NDebugger::enable(NDebugger::DETECT, VERSIONPRESS_PLUGIN_DIR . '/log');

if(file_exists(VERSIONPRESS_PLUGIN_DIR . '/.active'))
    require_once(VERSIONPRESS_PLUGIN_DIR . '/init.php');
