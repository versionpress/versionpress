<?php

namespace VersionPress\Actions;

use Symfony\Component\Yaml\Yaml;
use VersionPress\Git\GitRepository;
use VersionPress\Utils\FileSystem;

/**
 * This class is useful for persisting `actions.yml`. It saves them into a specified directory (not tracked by git);
 * therefore, VersionPress can display changes even for actions caused by plugins that are no longer installed.
 */
class ActionsDefinitionRepository
{
    /** @var string */
    private $directory;

    public function __construct($directory)
    {
        $this->directory = $directory;
    }

    public function getAllDefinitionFiles()
    {
        return new \GlobIterator($this->directory . '/*-actions.yml', \FilesystemIterator::CURRENT_AS_PATHNAME);
    }

    public function saveDefinitionForPlugin($plugin)
    {
        $actionsFile = dirname(WP_PLUGIN_DIR . '/' . $plugin) . '/.versionpress/actions.yml';

        if (!is_file($actionsFile)) {
            return;
        }

        $targetFile = $this->directory . '/' . $this->sanitizePluginName($plugin) . '-actions.yml';
        FileSystem::copy($actionsFile, $targetFile);
    }

    private function sanitizePluginName($pluginName)
    {
        return str_replace('/', '---', $pluginName);
    }
}
