<?php

namespace VersionPress\Actions;

use Symfony\Component\Yaml\Yaml;

class ActivePluginsActionsFilesIterator implements \IteratorAggregate
{
    public function getIterator()
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = wp_get_active_and_valid_plugins();

        foreach ($plugins as $pluginFile) {
            $pluginDir = dirname($pluginFile);
            $actionsFile = $pluginDir . '/.versionpress/actions.yml';
            if (file_exists($actionsFile)) {
                yield Yaml::parse($actionsFile);
            }
        }
    }
}
