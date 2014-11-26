<?php

class SynchronizationProcess {

    /**
     * @var SynchronizerFactory
     */
    private $synchronizerFactory;

    function __construct(SynchronizerFactory $synchronizerFactory) {
        $this->synchronizerFactory = $synchronizerFactory;
    }

    /**
     * Runs synchronization for given entities.
     * Takes array of entities or one or more string parameters.
     * E.g. $synchronizationProcess->synchronize(array('posts', 'comments'));
     * or   $synchronizationProcess->synchronize('posts', 'comments');
     * @param array|string $synchronizationSequence
     */
    function synchronize($synchronizationSequence) {
        if (!is_array($synchronizationSequence)){
            $synchronizationSequence = func_get_args();
        }

        foreach ($synchronizationSequence as $synchronizerName) {
            $this->synchronizerFactory
                ->createSynchronizer($synchronizerName)
                ->synchronize();
        }
    }
}