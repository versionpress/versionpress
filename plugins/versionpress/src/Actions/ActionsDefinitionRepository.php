<?php

namespace VersionPress\Actions;

use Symfony\Component\Yaml\Yaml;
use VersionPress\Git\GitRepository;
use VersionPress\Utils\FileSystem;

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

        $targetFile = $this->directory . '/' . $plugin . '-actions.yml';
        FileSystem::copy($actionsFile, $targetFile);
    }
}
