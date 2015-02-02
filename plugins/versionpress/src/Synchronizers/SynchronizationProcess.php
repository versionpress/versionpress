<?php

namespace VersionPress\Synchronizers;

use VersionPress\Utils\ArrayUtils;

class SynchronizationProcess {

    /**
     * @var SynchronizerFactory
     */
    private $synchronizerFactory;

    private $defaultSynchronizationSequence = array('option', 'user', 'usermeta', 'post', 'postmeta', 'comment', 'term', 'term_taxonomy', 'term_relationship');

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

        foreach ($synchronizationSequence as $synchronizerName) {
            $this->synchronizerFactory
                ->createSynchronizer($synchronizerName)
                ->synchronize();
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