<?php

class StorageFactory {

    private $vpdbDir;
    private $dbSchemaInfo;

    private $storageClassInfo = array();

    private $storages = array();

    /**
     * @param string $vpdbDir Path to the `wp-content/vpdb` directory
     * @param DbSchemaInfo $dbSchemaInfo Passed to storages
     */
    function __construct($vpdbDir, $dbSchemaInfo) {
        $this->vpdbDir = $vpdbDir;
        $this->initStorageClasses();
        $this->dbSchemaInfo = $dbSchemaInfo;
    }

    /**
     * Returns storage by given entity type
     *
     * @param string $entityName
     * @return Storage|null
     */
    public function getStorage($entityName) {
        if (isset($this->storages[$entityName]))
            return $this->storages[$entityName];

        $storageClass = $this->getStorageClass($entityName);
        $storagePath = $this->getStoragePath($entityName);
        if (class_exists($storageClass)) {
            $storage = new $storageClass($storagePath, $this->dbSchemaInfo->getEntityInfo($entityName));
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

    private function addStorageClassInfo($entityName, $className, $storagePath) {
        $this->storageClassInfo[$entityName] = array(
            'class' => $className,
            'path' => $this->vpdbDir . $storagePath
        );
    }

    private function getStorageClass($entityName) {
        if (!isset($this->storageClassInfo[$entityName])) {
            return null;
        }
        return $this->storageClassInfo[$entityName]['class'];
    }

    private function getStoragePath($entityName) {
        if (!isset($this->storageClassInfo[$entityName])) {
            return null;
        }
        return $this->storageClassInfo[$entityName]['path'];
    }
}