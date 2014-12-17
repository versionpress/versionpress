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
        static $synchronizerClasses = array(
            'post' => 'VersionPress\Synchronizers\PostsSynchronizer',
            'postmeta' => 'VersionPress\Synchronizers\PostMetaSynchronizer',
            'comment' => 'VersionPress\Synchronizers\CommentsSynchronizer',
            'option' => 'VersionPress\Synchronizers\OptionsSynchronizer',
            'user' => 'VersionPress\Synchronizers\UsersSynchronizer',
            'usermeta' => 'VersionPress\Synchronizers\UserMetaSynchronizer',
            'term' => 'VersionPress\Synchronizers\TermsSynchronizer',
            'term_taxonomy' => 'VersionPress\Synchronizers\TermTaxonomySynchronizer',
            'term_relationship' => 'VersionPress\Synchronizers\TermRelationshipsSynchronizer',
        );

        $synchronizerClass = $synchronizerClasses[$synchronizerName];
        return new $synchronizerClass($this->getStorage($synchronizerName), $this->database, $this->dbSchema);
    }

    private function getStorage($synchronizerName) {
        static $synchronizerStorages = array(
            'term_relationship' => 'post'
        );

        return $this->storageFactory->getStorage(isset($synchronizerStorages[$synchronizerName]) ? $synchronizerStorages[$synchronizerName] : $synchronizerName);
    }
}