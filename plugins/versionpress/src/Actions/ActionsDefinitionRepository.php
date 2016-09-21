<?php

namespace VersionPress\Actions;

use Symfony\Component\Yaml\Yaml;
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

    public function saveDefinitionForPlugin($pluginFile)
    {
        $pluginSlug = basename(dirname($pluginFile));
        $actionsFile = WP_PLUGIN_DIR . '/' . $pluginSlug . '/.versionpress/actions.yml';

        if (!is_file($actionsFile)) {
            return;
        }

        $targetFile = $this->getDefinitionFileName($pluginSlug);
        FileSystem::copy($actionsFile, $targetFile);
    }

    public function restoreAllDefinitionFilesFromHistory()
    {
        FileSystem::removeContent($this->directory);

        $definitionFilesWildcard = WP_PLUGIN_DIR . '/*/.versionpress/actions.yml';
        $modifications = $this->gitRepository->getFileModifications($definitionFilesWildcard);

        $modifications = array_filter($modifications, function ($modification) { return $modification['status'] !== 'D'; });
        $lastModifications = ArrayUtils::unique($modifications, function ($modification) { return $modification['path']; });

        foreach ($lastModifications as $modification) {
            $fileContent = $this->gitRepository->getFileInRevision($modification['path'], $modification['commit']);
            $plugin = basename(dirname(dirname($modification['path'])));

            $targetFile = $this->getDefinitionFileName($plugin);
            file_put_contents($targetFile, $fileContent);
        }

        $this->saveDefinitionForPlugin('versionpress/versionpress.php');
    }

    private function sanitizePluginName($pluginName)
    {
        return str_replace('/', '---', $pluginName);
    }

    private function getDefinitionFileName($plugin)
    {
        return $this->directory . '/' . $this->sanitizePluginName($plugin) . '-actions.yml';
    }
}
