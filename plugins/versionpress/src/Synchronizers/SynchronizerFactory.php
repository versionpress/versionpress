<?php

namespace VersionPress\Synchronizers;

use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\ShortcodesReplacer;
use VersionPress\Storages\StorageFactory;
use VersionPress\Utils\AbsoluteUrlReplacer;
use wpdb;

class SynchronizerFactory {
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

    /** @var AbsoluteUrlReplacer */
    private $urlReplacer;

    /** @var ShortcodesReplacer */
    private $shortcodesReplacer;

    private $synchronizerClasses = array(
        'post' => 'VersionPress\Synchronizers\PostsSynchronizer',
        'postmeta' => 'VersionPress\Synchronizers\PostMetaSynchronizer',
        'comment' => 'VersionPress\Synchronizers\CommentsSynchronizer',
        'commentmeta' => 'VersionPress\Synchronizers\CommentMetaSynchronizer',
        'option' => 'VersionPress\Synchronizers\OptionsSynchronizer',
        'user' => 'VersionPress\Synchronizers\UsersSynchronizer',
        'usermeta' => 'VersionPress\Synchronizers\UserMetaSynchronizer',
        'term' => 'VersionPress\Synchronizers\TermsSynchronizer',
        'termmeta' => 'VersionPress\Synchronizers\TermMetaSynchronizer',
        'term_taxonomy' => 'VersionPress\Synchronizers\TermTaxonomiesSynchronizer',
    );
    private $synchronizationSequence = array('user', 'usermeta', 'term', 'termmeta', 'term_taxonomy', 'post', 'postmeta', 'comment', 'commentmeta', 'option');

    function __construct(StorageFactory $storageFactory, Database $database, DbSchemaInfo $dbSchema, AbsoluteUrlReplacer $urlReplacer, ShortcodesReplacer $shortcodesReplacer) {
        $this->storageFactory = $storageFactory;
        $this->database = $database;
        $this->dbSchema = $dbSchema;
        $this->urlReplacer = $urlReplacer;
        $this->shortcodesReplacer = $shortcodesReplacer;
        $this->adjustSynchronizationSequenceToDbVersion();
    }

    /**
     * @param $synchronizerName
     * @return Synchronizer
     */
    public function createSynchronizer($synchronizerName) {
        $synchronizerClass = $this->synchronizerClasses[$synchronizerName];
        return new $synchronizerClass($this->getStorage($synchronizerName), $this->database, $this->dbSchema, $this->urlReplacer, $this->shortcodesReplacer);
    }

    public function getSynchronizationSequence() {
        return $this->synchronizationSequence;
    }

    private function getStorage($synchronizerName) {
        return $this->storageFactory->getStorage($synchronizerName);
    }

    private function adjustSynchronizationSequenceToDbVersion() {
        $allSupportedEntities = $this->dbSchema->getAllEntityNames();
        $this->synchronizationSequence = array_intersect($this->synchronizationSequence, $allSupportedEntities);
    }
}
