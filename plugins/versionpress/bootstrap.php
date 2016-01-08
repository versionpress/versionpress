<?php
use Nette\Caching\Storages\FileStorage;
use Nette\Loaders\RobotLoader;
use Tracy\Debugger;
use VersionPress\DI\DIContainer;

define('VERSIONPRESS_PLUGIN_DIR', __DIR__);
define('VERSIONPRESS_MIRRORING_DIR', WP_CONTENT_DIR . '/vpdb');
define('VERSIONPRESS_TEMP_DIR', VERSIONPRESS_PLUGIN_DIR . '/temp');
define('VERSIONPRESS_ACTIVATION_FILE', VERSIONPRESS_MIRRORING_DIR . '/.active');

require_once(VERSIONPRESS_PLUGIN_DIR . '/vendor/autoload.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/versionpress-functions.php');

if (defined('DOING_AJAX')) {
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
}

Debugger::enable(Debugger::DEVELOPMENT, VERSIONPRESS_PLUGIN_DIR . '/log');

$robotLoader = new RobotLoader();
$robotLoader->addDirectory(VERSIONPRESS_PLUGIN_DIR . '/src');
$robotLoader->setCacheStorage(new FileStorage(VERSIONPRESS_PLUGIN_DIR . '/temp'));
$robotLoader->register();

global $versionPressContainer;
$versionPressContainer = DIContainer::getConfiguredInstance();
