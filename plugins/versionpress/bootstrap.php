<?php
use Tracy\Debugger;
use VersionPress\DI\DIContainer;

/**
 * Absolute path to the directory containing VersionPress (wp-content/versionpress).
 */
define('VERSIONPRESS_PLUGIN_DIR', __DIR__);

if (!defined('VP_VPDB_DIR')) {
    /**
     * Absolute path to the directory where VersionPress saves versioned database data.
     */
    define('VP_VPDB_DIR', WP_CONTENT_DIR . '/vpdb');
}

/**
 * Absolute path to the directory where VersionPress saves temporary data, e.g. mutex locks.
 */
define('VERSIONPRESS_TEMP_DIR', VERSIONPRESS_PLUGIN_DIR . '/temp');

/**
 * Absolute path to the activation file.
 */
define('VERSIONPRESS_ACTIVATION_FILE', VP_VPDB_DIR . '/.active');

if (!defined('VP_PROJECT_ROOT')) {
    /**
     * Absolute path to the root of project - directory with .git.
     */
    define('VP_PROJECT_ROOT', ABSPATH);
}

if (!defined('VP_GIT_BINARY')) {
    /**
     * Absolute path to the git executable. Useful if it's not in PATH.
     */
    define('VP_GIT_BINARY', 'git');
}

if (!defined('VERSIONPRESS_GUI')) {
    /**
     * Which GUI to use. Used mainly for testing.
     *
     * Possible values: 'html' (VP 1.0), 'javascript' (VP 2.0 and later; React SPA)
     */
    define('VERSIONPRESS_GUI', 'javascript');
}

if (!defined('VERSIONPRESS_REQUIRE_API_AUTH')) {
    /**
     * Enables / disables authentication in API. Useful for standalone testing of frontend (React SPA).
     */
    define('VERSIONPRESS_REQUIRE_API_AUTH', true);
}

if (!defined('VERSIONPRESS_COMMIT_MESSAGE_PREFIX')) {
    /**
     * Prefix added in front of each commit message subject created by VersionPress
     */
    define('VERSIONPRESS_COMMIT_MESSAGE_PREFIX', '[VP] ');
}

require_once(VERSIONPRESS_PLUGIN_DIR . '/vendor/autoload.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/internal-functions.php');

if (defined('DOING_AJAX')) {
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
}

Debugger::enable(Debugger::DEVELOPMENT, VERSIONPRESS_PLUGIN_DIR . '/log');

global $versionPressContainer;
$versionPressContainer = DIContainer::getConfiguredInstance();
