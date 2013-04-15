<?php

class EntityStorageFactory {

    /**
     * @var string
     */
    private $storageDir;

    /**
     * @var string
     */
    private $tablePrefix;

    private $storageClasses = array();

    function __construct($storageDir, $tablePrefix) {
        $this->storageDir = $storageDir;
        $this->tablePrefix = $tablePrefix;
        $this->initStorageClasses();
    }

    /**
     * Returns storage by given entity type
     * @param $entityType string
     * @return EntityStorage
     */
    public function getStorage($entityType) {
        $entityStorageClass = $this->getStorageClass($entityType);
        $entityStorageDirectory = $this->getStorageDirectory($entityType);
        if(class_exists($entityStorageClass))
            return new $entityStorageClass($entityStorageDirectory);
        return null;
    }

    private function initStorageClasses() {
        $this->addStorageClassInfo('posts', 'PostStorage', '/posts');
    }

    private function addStorageClassInfo($entityName, $className, $storageDirectory){
        $this->storageClasses[$this->tablePrefix . $entityName] = array(
            'class' => $className,
            'directory' => $this->storageDir . $storageDirectory
        );
    }

    private function getStorageClass($entityType) {
        return $this->storageClasses[$entityType]['class'];
    }

    private function getStorageDirectory($entityType) {
        return $this->storageClasses[$entityType]['directory'];
    }
}