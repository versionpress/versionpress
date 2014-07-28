<?php
define('VERSIONPRESS_PLUGIN_DIR', dirname(__FILE__) . '/plugins/versionpress');
define('VERSIONPRESS_MIRRORING_DIR', VERSIONPRESS_PLUGIN_DIR . '/db');

require_once(VERSIONPRESS_PLUGIN_DIR . '/libs/nette.min.php');
NDebugger::enable(NDebugger::DETECT, VERSIONPRESS_PLUGIN_DIR . '/log');

$robotLoader = new NRobotLoader();
$robotLoader->addDirectory(VERSIONPRESS_PLUGIN_DIR . '/src');
$robotLoader->setCacheStorage(new NFileStorage(VERSIONPRESS_PLUGIN_DIR . '/temp'));
$robotLoader->register();

global $wpdb, $versionPressContainer;
$versionPressContainer = DIContainer::getConfiguredInstance();

if(file_exists(VERSIONPRESS_PLUGIN_DIR . '/.active'))
    $wpdb = $versionPressContainer->resolve(VersionPressServices::DATABASE);
