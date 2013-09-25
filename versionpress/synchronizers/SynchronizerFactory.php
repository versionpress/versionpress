<?php

class SynchronizerFactory {
    /**
     * @var EntityStorageFactory
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

    function __construct(EntityStorageFactory $storageFactory, wpdb $database, DbSchemaInfo $dbSchema) {
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
            'posts' => 'PostsSynchronizer',
            'comments' => 'CommentsSynchronizer',
            'options' => 'OptionsSynchronizer',
            'users' => 'UsersSynchronizer',
            'usermeta' => 'UserMetaSynchronizer',
            'terms' => 'TermsSynchronizer',
            'term_taxonomy' => 'TermTaxonomySynchronizer',
            'term_relationships' => 'TermRelationshipsSynchronizer',
        );

        $synchronizerClass = $synchronizerClasses[$synchronizerName];
        return new $synchronizerClass($this->getStorage($synchronizerName), $this->database, $this->dbSchema);
    }

    private function getStorage($synchronizerName) {
        static $synchronizerStorages = array(
            'term_relationships' => 'posts'
        );

        return $this->storageFactory->getStorage(isset($synchronizerStorages[$synchronizerName]) ? $synchronizerStorages[$synchronizerName] : $synchronizerName);
    }
}