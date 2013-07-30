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
            'users' => 'UsersSynchronizer'
        );

        $synchronizerClass = $synchronizerClasses[$synchronizerName];
        return new $synchronizerClass($this->storageFactory->getStorage($synchronizerName), $this->database, $this->dbSchema);
    }
}