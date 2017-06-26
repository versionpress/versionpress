<?php

namespace VersionPress\Actions;

use VersionPress\Git\GitRepository;
use VersionPress\Utils\ArrayUtils;
use VersionPress\Utils\FileSystem;

/**
 * This class is useful for persisting `actions.yml`. It saves them into a specified directory (not tracked by git);
 * therefore, VersionPress can display changes even for actions caused by plugins that are no longer installed.
 */
class ActionsDefinitionRepository
{
    /** @var string */
    private $directory;
    /** @var GitRepository */
    private $gitRepository;

    public function __construct($directory, $gitRepository)
    {
        $this->directory = $directory;
        $this->gitRepository = $gitRepository;
    }

    public function getAllDefinitionFiles()
    {
        return new \GlobIterator($this->directory . '/*-actions.yml', \FilesystemIterator::CURRENT_AS_PATHNAME);
    }

    public function saveActionsFileForPlugin($pluginFile)
    {
        $pluginSlug = basename(dirname($pluginFile));
        $actionsFile = PluginDefinitionDiscovery::getPathForPlugin($pluginSlug, 'actions.yml');

        if (!$actionsFile) {
            return;
        }

        $targetFile = $this->getActionsFileName($pluginSlug);
        FileSystem::copy($actionsFile, $targetFile);
    }

    public function restoreAllActionsFilesFromHistory()
    {
        FileSystem::removeContent($this->directory);

        $this->restoreActionFilesByWildcard(WP_PLUGIN_DIR . '/*/.versionpress/actions.yml');
        $this->restoreActionFilesByWildcard(WP_CONTENT_DIR . '/.versionpress/*/actions.yml');

        $this->saveActionsFileForPlugin('versionpress/versionpress.php');
    }

    private function sanitizePluginName($pluginName)
    {
        return str_replace('/', '---', $pluginName);
    }

    private function getActionsFileName($plugin)
    {
        return $this->directory . '/' . $this->sanitizePluginName($plugin) . '-actions.yml';
    }

    private function restoreActionFilesByWildcard($definitionFilesWildcard)
    {
        $modifications = $this->gitRepository->getFileModifications($definitionFilesWildcard);

        $deletions = array_filter($modifications, function ($modification) {
            return $modification['status'] !== 'D';
        });

        $lastDeletions = ArrayUtils::unique($deletions, function ($modification) {
            return $modification['path'];
        });

        foreach ($lastDeletions as $deletion) {
            $fileContent = $this->gitRepository->getFileInRevision($deletion['path'], $deletion['commit']);
            $plugin = basename(dirname(dirname($deletion['path'])));

            $targetFile = $this->getActionsFileName($plugin);
            file_put_contents($targetFile, $fileContent);
        }
    }
}
