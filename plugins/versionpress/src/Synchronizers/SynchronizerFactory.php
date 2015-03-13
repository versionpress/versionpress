<?php

namespace VersionPress\Synchronizers;

use VersionPress\Database\DbSchemaInfo;
use VersionPress\Storages\StorageFactory;
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

    private $synchronizerClasses = array(
        'post' => 'VersionPress\Synchronizers\PostsSynchronizer',
        'postmeta' => 'VersionPress\Synchronizers\PostMetaSynchronizer',
        'comment' => 'VersionPress\Synchronizers\CommentsSynchronizer',
        'option' => 'VersionPress\Synchronizers\OptionsSynchronizer',
        'user' => 'VersionPress\Synchronizers\UsersSynchronizer',
        'usermeta' => 'VersionPress\Synchronizers\UserMetaSynchronizer',
        'term' => 'VersionPress\Synchronizers\TermsSynchronizer',
        'term_taxonomy' => 'VersionPress\Synchronizers\TermTaxonomySynchronizer',
    );

    function __construct(StorageFactory $storageFactory, wpdb $database, DbSchemaInfo $dbSchema) {
        $this->storageFactory = $storageFactory;
        $this->database = $database;
        $this->dbSchema = $dbSchema;
    }

    /**
     * @param $synchronizerName
     * @return Synchronizer
     */
    public function createSynchronizer($synchronizerName) {
        $synchronizerClass = $this->synchronizerClasses[$synchronizerName];
        return new $synchronizerClass($this->getStorage($synchronizerName), $this->database, $this->dbSchema);
    }

    public function getAllSupportedSynchronizers() {
        return array_keys($this->synchronizerClasses);
    }

    private function getStorage($synchronizerName) {
        return $this->storageFactory->getStorage($synchronizerName);
    }
}