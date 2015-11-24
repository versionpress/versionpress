<?php

namespace VersionPress\Synchronizers;

use VersionPress\Database\DbSchemaInfo;
use VersionPress\Storages\StorageFactory;
use VersionPress\Utils\AbsoluteUrlReplacer;
use wpdb;

class SynchronizerFactory {
    /**
     * @var StorageFactory
     */
    private $storageFactory;
    /**
     * @var wpdb
     */
    private $database;
    /**
     * @var DbSchemaInfo
     */
    private $dbSchema;

    /** @var AbsoluteUrlReplacer */
    private $urlReplacer;

    private $synchronizerClasses = array(
        'post' => 'VersionPress\Synchronizers\PostsSynchronizer',
        'postmeta' => 'VersionPress\Synchronizers\PostMetaSynchronizer',
        'comment' => 'VersionPress\Synchronizers\CommentsSynchronizer',
        'option' => 'VersionPress\Synchronizers\OptionsSynchronizer',
        'user' => 'VersionPress\Synchronizers\UsersSynchronizer',
        'usermeta' => 'VersionPress\Synchronizers\UserMetaSynchronizer',
        'term' => 'VersionPress\Synchronizers\TermsSynchronizer',
        'term_taxonomy' => 'VersionPress\Synchronizers\TermTaxonomiesSynchronizer',
    );

    private $synchronizationSequence = array('option', 'user', 'usermeta', 'term', 'term_taxonomy', 'post', 'postmeta', 'comment');

    function __construct(StorageFactory $storageFactory, $wpdb, DbSchemaInfo $dbSchema, AbsoluteUrlReplacer $urlReplacer) {
        $this->storageFactory = $storageFactory;
        $this->database = $wpdb;
        $this->dbSchema = $dbSchema;
        $this->urlReplacer = $urlReplacer;
    }

    /**
     * @param $synchronizerName
     * @return Synchronizer
     */
    public function createSynchronizer($synchronizerName) {
        $synchronizerClass = $this->synchronizerClasses[$synchronizerName];
        return new $synchronizerClass($this->getStorage($synchronizerName), $this->database, $this->dbSchema, $this->urlReplacer);
    }

    public function getSynchronizationSequence() {
        return $this->synchronizationSequence;
    }

    private function getStorage($synchronizerName) {
        return $this->storageFactory->getStorage($synchronizerName);
    }
}