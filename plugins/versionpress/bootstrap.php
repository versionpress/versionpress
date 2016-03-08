<?php
use Tracy\Debugger;
use VersionPress\DI\DIContainer;

define('VERSIONPRESS_PLUGIN_DIR', __DIR__);

if (!defined('VP_VPDB_DIR')) {
    define('VP_VPDB_DIR', WP_CONTENT_DIR . '/vpdb');
}

define('VERSIONPRESS_TEMP_DIR', VERSIONPRESS_PLUGIN_DIR . '/temp');
define('VERSIONPRESS_ACTIVATION_FILE', VP_VPDB_DIR . '/.active');

if (!defined('VP_PROJECT_ROOT')) {
    define('VP_PROJECT_ROOT', ABSPATH);
}

require_once(VERSIONPRESS_PLUGIN_DIR . '/vendor/autoload.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/versionpress-functions.php');

if (defined('DOING_AJAX')) {
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
}

Debugger::enable(Debugger::DEVELOPMENT, VERSIONPRESS_PLUGIN_DIR . '/log');

global $versionPressContainer;
$versionPressContainer = DIContainer::getConfiguredInstance();
