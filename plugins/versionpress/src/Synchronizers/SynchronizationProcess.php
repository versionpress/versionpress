<?php

namespace VersionPress\Synchronizers;

use VersionPress\Utils\ArrayUtils;

class SynchronizationProcess {

    /**
     * @var SynchronizerFactory
     */
    private $synchronizerFactory;

    private static $defaultSynchronizationSequence = array('storages' => array('option', 'user', 'usermeta', 'term', 'term_taxonomy', 'post', 'postmeta', 'comment'));

    function __construct(SynchronizerFactory $synchronizerFactory) {
        $this->synchronizerFactory = $synchronizerFactory;
    }

    /**
     * Runs synchronization for managed entities. Takes storages and entities which will be synchronized.
     *
     * @param array|null $entitiesToSynchronize List of entities which will be synchronized
     *          For example [storages => [options, terms], entities => [[vp_id => VPID1, parent => VPID2], ...]].
     */
    function synchronize($entitiesToSynchronize = null) {
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        @set_time_limit(0); // intentionally @ - if it's disabled we can't do anything but try the synchronization

        if ($entitiesToSynchronize === null) {
            $entitiesToSynchronize = self::$defaultSynchronizationSequence;
        }

        $storageSynchronizationSequence = $this->sortStoragesToSynchronize($entitiesToSynchronize['storages']);
        $synchronizerFactory = $this->synchronizerFactory;

        $synchronizationTasks = array();

        if (isset($entitiesToSynchronize['entities'])) {
            $allSynchronizers = $synchronizerFactory->getAllSupportedSynchronizers();
            $synchronizationTasks = array_merge($synchronizationTasks, array_map(function ($synchronizerName) use ($entitiesToSynchronize, $synchronizerFactory) {
                $synchronizer = $synchronizerFactory->createSynchronizer($synchronizerName);
                return array('synchronizer' => $synchronizer, 'task' => Synchronizer::SYNCHRONIZE_EVERYTHING, 'entities' => $entitiesToSynchronize['entities']);
            }, $allSynchronizers));
        }

        $synchronizationTasks = array_merge($synchronizationTasks, array_map(function ($synchronizerName) use ($synchronizerFactory) {
            $synchronizer = $synchronizerFactory->createSynchronizer($synchronizerName);
            return array ('synchronizer' => $synchronizer, 'task' => Synchronizer::SYNCHRONIZE_EVERYTHING, 'entities' => null);
        }, $storageSynchronizationSequence));

        while (count($synchronizationTasks) > 0) {
            $task = array_shift($synchronizationTasks);
            /** @var Synchronizer $synchronizer */
            $synchronizer = $task['synchronizer'];
            $remainingTasks = $synchronizer->synchronize($task['task'], $task['entities']);

            foreach ($remainingTasks as $remainingTask) {
                $synchronizationTasks[] = array('synchronizer' => $synchronizer, 'task' => $remainingTask, 'entities' => $task['entities']);
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
    public static function sortStoragesToSynchronize($entitiesToSynchronize) {
        $defaultSynchronizationSequence = self::$defaultSynchronizationSequence['storages'];
        $entitiesToSynchronize = array_unique($entitiesToSynchronize);

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