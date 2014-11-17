<?php

class StorageFactory {

    /**
     * @var string
     */
    private $storageDir;

    private $storageClassInfo = array();

    private $storages = array();

    function __construct($storageDir) {
        $this->storageDir = $storageDir;
        $this->initStorageClasses();
    }

    /**
     * Returns storage by given entity type
     * @param string $entityName
     * @return Storage
     */
    public function getStorage($entityName) {
        if (isset($this->storages[$entityName]))
            return $this->storages[$entityName];

        $storageClass = $this->getStorageClass($entityName);
        $storageDirectory = $this->getStorageDirectory($entityName);
        if (class_exists($storageClass)){
            $storage = new $storageClass($storageDirectory);
            $this->storages[$entityName] = $storage;
            return $storage;
        }
        return null;
    }

    public function getAllSupportedStorages() {
        return array_keys($this->storageClassInfo);
    }

    private function initStorageClasses() {
        $this->addStorageClassInfo('posts', 'PostStorage', '/posts');
        $this->addStorageClassInfo('comments', 'CommentStorage', '/comments');
        $this->addStorageClassInfo('options', 'OptionsStorage', '/options.ini');
        $this->addStorageClassInfo('terms', 'TermsStorage', '/terms.ini');
        $this->addStorageClassInfo('term_taxonomy', 'TermTaxonomyStorage', '/terms.ini');
        $this->addStorageClassInfo('users', 'UserStorage', '/users.ini');
        $this->addStorageClassInfo('usermeta', 'UserMetaStorage', '/users.ini');
        $this->addStorageClassInfo('postmeta', 'PostMetaStorage', '/posts');
    }

    private function addStorageClassInfo($entityName, $className, $storageDirectory) {
        $this->storageClassInfo[$entityName] = array(
            'class' => $className,
            'directory' => $this->storageDir . $storageDirectory
        );
    }

    private function getStorageClass($entityName) {
        if(!isset($this->storageClassInfo[$entityName])) return null;
        return $this->storageClassInfo[$entityName]['class'];
    }

    private function getStorageDirectory($entityName) {
        if(!isset($this->storageClassInfo[$entityName])) return null;
        return $this->storageClassInfo[$entityName]['directory'];
    }
}