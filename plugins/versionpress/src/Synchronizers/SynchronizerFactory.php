<?php

namespace VersionPress\Synchronizers;

use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\ShortcodesReplacer;
use VersionPress\Database\TableSchemaStorage;
use VersionPress\Database\VpidRepository;
use VersionPress\Storages\StorageFactory;
use VersionPress\Utils\AbsoluteUrlReplacer;
use wpdb;

class SynchronizerFactory
{
    /**
     * @var StorageFactory
     */
    private $storageFactory;
    /**
     * @var Database
     */
    private $database;
    /**
     * @var DbSchemaInfo
     */
    private $dbSchema;

    /** @var VpidRepository */
    private $vpidRepository;

    /** @var AbsoluteUrlReplacer */
    private $urlReplacer;

    /** @var ShortcodesReplacer */
    private $shortcodesReplacer;

    private $synchronizationSequence = [];

    /** @var TableSchemaStorage */
    private $tableSchemaStorage;

    public function __construct(
        StorageFactory $storageFactory,
        Database $database,
        DbSchemaInfo $dbSchema,
        VpidRepository $vpidRepository,
        AbsoluteUrlReplacer $urlReplacer,
        ShortcodesReplacer $shortcodesReplacer,
        TableSchemaStorage $tableSchemaStorage
    ) {
        $this->storageFactory = $storageFactory;
        $this->database = $database;
        $this->dbSchema = $dbSchema;
        $this->vpidRepository = $vpidRepository;
        $this->urlReplacer = $urlReplacer;
        $this->shortcodesReplacer = $shortcodesReplacer;
        $this->synchronizationSequence = $this->resolveSynchronizationSequence();
        $this->tableSchemaStorage = $tableSchemaStorage;
    }

    /**
     * @param $entityName
     * @return Synchronizer
     */
    public function createSynchronizer($entityName)
    {
        return new Synchronizer(
            $this->getStorage($entityName),
            $this->database,
            $this->dbSchema->getEntityInfo($entityName),
            $this->dbSchema,
            $this->vpidRepository,
            $this->urlReplacer,
            $this->shortcodesReplacer,
            $this->tableSchemaStorage
        );
    }

    public function getSynchronizationSequence()
    {
        return $this->synchronizationSequence;
    }

    private function getStorage($synchronizerName)
    {
        return $this->storageFactory->getStorage($synchronizerName);
    }

    /**
     * Determines sequence in which entities should be synchronized.
     * It's based on their dependencies on other entities.
     *
     * @return array
     */
    private function resolveSynchronizationSequence()
    {
        $unresolved = $this->dbSchema->getAllReferences();
        $sequence = [];
        $triedRemovingSelfReferences = false;


        while (count($unresolved) > 0) {
            $resolvedInThisStep = [];

            // 1st step - move all entities with resolved dependencies to $sequence
            foreach ($unresolved as $entity => $deps) {
                if (count($deps) === 0) {
                    unset($unresolved[$entity]);
                    $sequence[] = $entity;
                    $resolvedInThisStep[] = $entity;
                }
            }

            // 2nd step - update unresolved dependencies of remaining entities
            foreach ($resolvedInThisStep as $resolvedEntity) {
                foreach ($unresolved as $unresolvedEntity => $deps) {
                    $unresolved[$unresolvedEntity] = array_diff($deps, [$resolvedEntity]);
                }
            }

            // Nothing changed - circular dependency
            if (count($resolvedInThisStep) === 0) {
                if (!$triedRemovingSelfReferences) {
                    // At first try to remove all self-references as they have to run in 2-pass sync anyway
                    $triedRemovingSelfReferences = true;
                    foreach ($unresolved as $unresolvedEntity => $deps) {
                        $unresolved[$unresolvedEntity] = array_diff($deps, [$unresolvedEntity]);
                    }
                } else {
                    // Simply eliminate dependencies one by one until it is resolvable
                    reset($unresolved);
                    $firstEntity = key($unresolved);
                    array_pop($unresolved[$firstEntity]);
                }
            }
        }

        return $sequence;
    }
}
