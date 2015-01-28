<?php
use Nette\Caching\Storages\FileStorage;
use Nette\Loaders\RobotLoader;
use Tracy\Debugger;
use VersionPress\DI\DIContainer;
use VersionPress\DI\VersionPressServices;

define('VERSIONPRESS_PLUGIN_DIR', __DIR__);
define('VERSIONPRESS_MIRRORING_DIR', WP_CONTENT_DIR . '/vpdb');
define('VERSIONPRESS_TEMP_DIR', VERSIONPRESS_PLUGIN_DIR . '/temp');
define('VERSIONPRESS_ACTIVATION_FILE', VERSIONPRESS_MIRRORING_DIR . '/.active');

/**
 * Nette is currently referenced as a minified library. We only need pieces from it so we should
 * ideally create a custom distribution at some point in the future.
 *
 * (Note: Nette 2.2 already uses the modular structure, however, it supports PHP 5.3 only. This might be
 * a problem for us, or not, see http://jira.agilio.cz/browse/WP-10 and http://jira.agilio.cz/browse/WP-40.
 */
require_once(VERSIONPRESS_PLUGIN_DIR . '/vendor/autoload.php');

if (defined('DOING_AJAX')) {
    header("Content-Type: application/json");
}

$ndebuggerMode = defined('WP_CLI') && WP_CLI ? Debugger::DEVELOPMENT : Debugger::DETECT;
Debugger::enable($ndebuggerMode, VERSIONPRESS_PLUGIN_DIR . '/log');

$robotLoader = new RobotLoader();
$robotLoader->addDirectory(VERSIONPRESS_PLUGIN_DIR . '/src');
$robotLoader->setCacheStorage(new FileStorage(VERSIONPRESS_PLUGIN_DIR . '/temp'));
$robotLoader->register();

global $wpdb, $versionPressContainer;
$versionPressContainer = DIContainer::getConfiguredInstance();

if (file_exists(VERSIONPRESS_ACTIVATION_FILE)) {
    $wpdb = $versionPressContainer->resolve(VersionPressServices::DATABASE);
}
