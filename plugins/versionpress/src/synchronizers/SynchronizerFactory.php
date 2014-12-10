<?php

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
            'post' => 'PostsSynchronizer',
            'postmeta' => 'PostMetaSynchronizer',
            'comment' => 'CommentsSynchronizer',
            'option' => 'OptionsSynchronizer',
            'user' => 'UsersSynchronizer',
            'usermeta' => 'UserMetaSynchronizer',
            'term' => 'TermsSynchronizer',
            'term_taxonomy' => 'TermTaxonomySynchronizer',
            'term_relationship' => 'TermRelationshipsSynchronizer',
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