<?php

namespace VersionPress\Actions;

/**
 * Iterator useful for getting VP-related files from all active plugins.
 * It takes name of the desired files (e.g. `schema.yml`, `actions.yml`) and then iterates only files with this name.
 *
 */
class ActivePluginsVPFilesIterator implements \IteratorAggregate
{
    private $iteratedFiles;

    public function __construct($iteratedFiles)
    {
        $this->iteratedFiles = $iteratedFiles;
    }

    public function getIterator()
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = get_option('active_plugins');

        foreach ($plugins as $pluginFile) {
            $pluginDir = WP_PLUGIN_DIR . '/' . dirname($pluginFile);
            $path = $pluginDir . '/.versionpress/' . $this->iteratedFiles;
            if (file_exists($path)) {
                yield $path;
            }
        }
    }
}
