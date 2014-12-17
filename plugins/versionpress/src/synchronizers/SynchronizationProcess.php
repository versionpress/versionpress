<?php

namespace VersionPress\Synchronizers;

class SynchronizationProcess {

    /**
     * @var SynchronizerFactory
     */
    private $synchronizerFactory;

    function __construct(SynchronizerFactory $synchronizerFactory) {
        $this->synchronizerFactory = $synchronizerFactory;
    }

    /**
     * Runs synchronization for managed entities.
     *
     * Note: it used to take an argument with an array of entitites which might be useful in the future
     * when we start supporting external entitities but it's currently not needed.
     */
    function synchronize() {

        $synchronizationSequence = array('option', 'user', 'usermeta', 'post', 'postmeta', 'comment', 'term', 'term_taxonomy', 'term_relationship');

        foreach ($synchronizationSequence as $synchronizerName) {
            $this->synchronizerFactory
                ->createSynchronizer($synchronizerName)
                ->synchronize();
        }
    }
}