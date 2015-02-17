<?php

namespace VersionPress\Synchronizers;

use VersionPress\Utils\ArrayUtils;

class SynchronizationProcess {

    /**
     * @var SynchronizerFactory
     */
    private $synchronizerFactory;

    private $defaultSynchronizationSequence = array('option', 'user', 'usermeta', 'post', 'postmeta', 'comment', 'term', 'term_taxonomy');

    function __construct(SynchronizerFactory $synchronizerFactory) {
        $this->synchronizerFactory = $synchronizerFactory;
    }

    /**
     * Runs synchronization for managed entities.
     *
     * @param string[]|null $entitiesToSynchronize List of entities which will be synchronized
     */
    function synchronize($entitiesToSynchronize = null) {

        if ($entitiesToSynchronize === null) {
            $entitiesToSynchronize = $this->defaultSynchronizationSequence;
        }

        $synchronizationSequence = $this->sortEntitiesToSynchronize($entitiesToSynchronize);
        $synchronizerFactory = $this->synchronizerFactory;
        $synchronizationTasks = array_map(function ($synchronizerName) use ($synchronizerFactory) {
            $synchronizer = $synchronizerFactory->createSynchronizer($synchronizerName);
            return array ('synchronizer' => $synchronizer, 'task' => Synchronizer::SYNCHRONIZE_EVERYTHING);
        }, $synchronizationSequence);

        while (count($synchronizationTasks) > 0) {
            $task = array_shift($synchronizationTasks);
            /** @var Synchronizer $synchronizer */
            $synchronizer = $task['synchronizer'];
            $result = $synchronizer->synchronize($task['task']);

            if ($result !== null) {
                $synchronizationTasks[] = array('synchronizer' => $synchronizer, 'task' => $result);
            }
        }
    }

    /**
     * Sorts given entities by the default sequence. It's necessary to keep the order because of references.
     * E.g. Options or users can be synchronized anytime because they have no references. However usermeta
     * has to be sorted always after users. Posts also after users (because of the author reference) etc.
     *
     * @param string[] $entitiesToSynchronize
     * @return string[]
     */
    private function sortEntitiesToSynchronize($entitiesToSynchronize) {
        $defaultSynchronizationSequence = $this->defaultSynchronizationSequence;
        ArrayUtils::stablesort($entitiesToSynchronize, function ($entity1, $entity2) use ($defaultSynchronizationSequence) {
            $priority1 = array_search($entity1, $defaultSynchronizationSequence);
            $priority2 = array_search($entity2, $defaultSynchronizationSequence);

            if ($priority1 < $priority2) {
                return -1;
            }

            if ($priority1 > $priority2) {
                return 1;
            }

            return 0;
        });

        return $entitiesToSynchronize;
    }
}