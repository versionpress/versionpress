<?php

class EntityStorageFactory {

    /**
     * @var string
     */
    private $storageDir;

    private $storageClasses = array();

    private $aliases = array();

    private $storages = array();

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
        $alias = $this->aliases[$entityType];
        if(isset($this->storages[$alias]))
            return $this->storages[$alias];

        $entityStorageClass = $this->getStorageClass($alias);
        $entityStorageDirectory = $this->getStorageDirectory($alias);
        if (class_exists($entityStorageClass)){
            $storage = new $entityStorageClass($entityStorageDirectory);
            $this->storages[$alias] = $storage;
            return $storage;
        }
        return null;
    }

    private function initStorageClasses() {
        $this->addStorageClassInfo('posts', 'PostStorage', '/posts');
        $this->addStorageClassInfo('comments', 'CommentStorage', '/comments');
        $this->addStorageClassInfo('options', 'OptionsStorage', '/options.ini');
        $this->addStorageClassInfo('terms', 'TermsStorage', '/terms.ini', array('term_taxonomy'));
    }

    private function addStorageClassInfo($entityName, $className, $storageDirectory, $aliases = array()) {

        $this->aliases[$entityName] = $entityName;
        foreach($aliases as $alias) {
            $this->aliases[$alias] = $entityName;
        }

        $this->storageClasses[$entityName] = array(
            'class' => $className,
            'directory' => $this->storageDir . $storageDirectory
        );
    }

    private function getStorageClass($alias) {
        return $this->storageClasses[$alias]['class'];
    }

    private function getStorageDirectory($alias) {
        return $this->storageClasses[$alias]['directory'];
    }
}