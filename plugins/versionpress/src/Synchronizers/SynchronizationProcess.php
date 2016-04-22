<?php

namespace VersionPress\Synchronizers;

class SynchronizationProcess
{

    /**
     * @var SynchronizerFactory
     */
    private $synchronizerFactory;

    public function __construct(SynchronizerFactory $synchronizerFactory)
    {
        $this->synchronizerFactory = $synchronizerFactory;
    }

    /**
     * Runs synchronization for managed entities.
     *
     * @param array $vpidsToSynchronize List of VPIDS of entities which will be synchronized.
     */
    public function synchronize(array $vpidsToSynchronize)
    {
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        @set_time_limit(0); // intentionally @ - if it's disabled we can't do anything but try the synchronization

        $synchronizerFactory = $this->synchronizerFactory;
        $allSynchronizers = $synchronizerFactory->getSynchronizationSequence();

        $synchronizationTasks = array_map(function ($synchronizerName) use ($vpidsToSynchronize, $synchronizerFactory) {
            $synchronizer = $synchronizerFactory->createSynchronizer($synchronizerName);
            return [
                'synchronizer' => $synchronizer,
                'task' => Synchronizer::SYNCHRONIZE_EVERYTHING,
                'entities' => $vpidsToSynchronize
            ];
        }, $allSynchronizers);

        $this->runSynchronizationTasks($synchronizationTasks);
    }

    /**
     * Runs synchronization for all managed entities.
     */
    public function synchronizeAll()
    {
        $synchronizerFactory = $this->synchronizerFactory;
        $synchronizationTasks = array_map(function ($synchronizerName) use ($synchronizerFactory) {
            $synchronizer = $synchronizerFactory->createSynchronizer($synchronizerName);
            return [
                'synchronizer' => $synchronizer,
                'task' => Synchronizer::SYNCHRONIZE_EVERYTHING,
                'entities' => null
            ];
        }, $synchronizerFactory->getSynchronizationSequence());

        $this->runSynchronizationTasks($synchronizationTasks);
    }

    private function runSynchronizationTasks(array $synchronizationTasks)
    {
        while (count($synchronizationTasks) > 0) {
            $task = array_shift($synchronizationTasks);
            /** @var Synchronizer $synchronizer */
            $synchronizer = $task['synchronizer'];
            $remainingTasks = $synchronizer->synchronize($task['task'], $task['entities']);

            foreach ($remainingTasks as $remainingTask) {
                $synchronizationTasks[] = [
                    'synchronizer' => $synchronizer,
                    'task' => $remainingTask,
                    'entities' => $task['entities']
                ];
            }
        }
    }
}
