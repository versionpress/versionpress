<?php

namespace VersionPress;

class VersionPress
{
    /**
     * Returns VersionPress version as specified in plugin metadata
     *
     * @return string
     */
    public static function getVersion()
    {
        if (!function_exists('get_plugin_data')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        $pluginData = get_plugin_data(VERSIONPRESS_PLUGIN_DIR . "/versionpress.php", false, false);
        return $pluginData['Version'];
    }

    /**
     * Returns name of current environment (master for original site, clone name for clone).
     *
     * @return string
     */
    public static function getEnvironment()
    {
        return defined('VP_ENVIRONMENT') ? VP_ENVIRONMENT : 'master';
    }

    /**
     * Returns true if VersionPress is active. Note that active != activated and being
     * active means that VersionPress is tracking changes.
     *
     * @return bool
     */
    public static function isActive()
    {
        return defined('VERSIONPRESS_ACTIVATION_FILE') && file_exists(VERSIONPRESS_ACTIVATION_FILE);
    }
}
