<?php

namespace VersionPress\Actions;

use Symfony\Component\Yaml\Yaml;
use VersionPress\Git\GitRepository;

class ActionsDefinitionRepository
{
    /** @var GitRepository */
    private $gitRepository;

    public function __construct(GitRepository $gitRepository)
    {
        $this->gitRepository = $gitRepository;
    }

    public function getAllDefinitions()
    {
        $actionsNote = $this->gitRepository->getNote($this->getInitialCommitHash());
        return Yaml::parse($actionsNote);
    }

    public function saveDefinitionForPlugin($plugin)
    {
        $actionsFile = dirname(WP_PLUGIN_DIR . '/' . $plugin) . '/.versionpress/actions.yml';

        if (!is_file($actionsFile)) {
            return;
        }

        $pluginActionsDefinition = Yaml::parse(file_get_contents($actionsFile));

        $actionsDefinitions = $this->getAllDefinitions();
        $actionsDefinitions[$plugin] = $pluginActionsDefinition;

        $yaml = Yaml::dump($actionsDefinitions);
        $this->gitRepository->saveNote($this->getInitialCommitHash(), $yaml);
    }

    private function getInitialCommitHash()
    {
        return $this->gitRepository->getInitialCommit()->getHash();
    }
}
