<?php

class EntityStorageFactory {

    /**
     * @var string
     */
    private $storageDir;

    private $storageClasses;

    function __construct($storageDir) {
        $this->storageDir = $storageDir;
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
        $this->storageClasses = array(
            'wp_posts' => array(
                'class' => 'PostStorage',
                'directory' => $this->storageDir . '/posts'
            )
        );
    }

    private function getStorageClass($entityType) {
        return $this->storageClasses[$entityType]['class'];
    }

    private function getStorageDirectory($entityType) {
        return $this->storageClasses[$entityType]['directory'];
    }
}