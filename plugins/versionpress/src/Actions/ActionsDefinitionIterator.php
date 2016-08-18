<?php

namespace VersionPress\Actions;

use Symfony\Component\Yaml\Yaml;
use VersionPress\Git\GitRepository;

class ActionsDefinitionIterator implements \IteratorAggregate
{
    /** @var ActionsDefinitionRepository */
    private $actionsNoteManager;

    public function __construct(ActionsDefinitionRepository $actionsNoteManager)
    {
        $this->actionsNoteManager = $actionsNoteManager;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->actionsNoteManager->getAllDefinitions());
    }
}
