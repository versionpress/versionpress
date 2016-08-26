<?php

namespace VersionPress\Synchronizers;

use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\ShortcodesReplacer;
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

    private $synchronizerClasses = [
        'post' => SynchronizerBase::class,
        'postmeta' => SynchronizerBase::class,
        'comment' => SynchronizerBase::class,
        'commentmeta' => SynchronizerBase::class,
        'option' => SynchronizerBase::class,
        'user' => SynchronizerBase::class,
        'usermeta' => SynchronizerBase::class,
        'term' => SynchronizerBase::class,
        'termmeta' => SynchronizerBase::class,
        'term_taxonomy' => SynchronizerBase::class,
    ];
    private $synchronizationSequence = [
        'user',
        'usermeta',
        'term',
        'termmeta',
        'term_taxonomy',
        'post',
        'postmeta',
        'comment',
        'commentmeta',
        'option'
    ];

    public function __construct(
        StorageFactory $storageFactory,
        Database $database,
        DbSchemaInfo $dbSchema,
        VpidRepository $vpidRepository,
        AbsoluteUrlReplacer $urlReplacer,
        ShortcodesReplacer $shortcodesReplacer
    ) {
        $this->storageFactory = $storageFactory;
        $this->database = $database;
        $this->dbSchema = $dbSchema;
        $this->vpidRepository = $vpidRepository;
        $this->urlReplacer = $urlReplacer;
        $this->shortcodesReplacer = $shortcodesReplacer;
        $this->adjustSynchronizationSequenceToDbVersion();
    }

    /**
     * @param $synchronizerName
     * @return Synchronizer
     */
    public function createSynchronizer($synchronizerName)
    {
        $synchronizerClass = $this->synchronizerClasses[$synchronizerName];
        return new $synchronizerClass($this->getStorage($synchronizerName), $this->database,
            $this->dbSchema->getEntityInfo($synchronizerName), $this->dbSchema, $this->vpidRepository,
            $this->urlReplacer, $this->shortcodesReplacer);
    }

    public function getSynchronizationSequence()
    {
        return $this->synchronizationSequence;
    }

    private function getStorage($synchronizerName)
    {
        return $this->storageFactory->getStorage($synchronizerName);
    }

    private function adjustSynchronizationSequenceToDbVersion()
    {
        $allSupportedEntities = $this->dbSchema->getAllEntityNames();
        $this->synchronizationSequence = array_intersect($this->synchronizationSequence, $allSupportedEntities);
    }
}
