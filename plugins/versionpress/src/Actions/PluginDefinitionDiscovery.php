<?php

namespace VersionPress\Actions;

/**
 * Class useful for getting definition (actions.yml, schema.yml, hooks.php, etc.) files of plugins.
 *
 */
class PluginDefinitionDiscovery
{
    public static function getPathForPlugin($pluginSlug, $definitionFile)
    {
        $globalDefinitionsDir = WP_CONTENT_DIR . '/.versionpress';

        $defaultDefinitionFile = $globalDefinitionsDir . '/' . $pluginSlug . '/' . $definitionFile;
        $pluginDirDefinitionFile = WP_PLUGIN_DIR . '/' . $pluginSlug . '/.versionpress/' . $definitionFile;

        if (file_exists($defaultDefinitionFile)) {
            return $defaultDefinitionFile;
        } elseif (file_exists($pluginDirDefinitionFile)) {
            return $pluginDirDefinitionFile;
        }

        return null;
    }

    public static function getPathsForActivePlugins($definitionFile)
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = get_option('active_plugins');

        foreach ($plugins as $pluginFile) {
            $pluginSlug = dirname($pluginFile);
            $definitionFilePath = self::getPathForPlugin($pluginSlug, $definitionFile);
            if ($definitionFilePath) {
                yield $definitionFilePath;
            }
        }
    }
}
