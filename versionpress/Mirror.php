<?php

class Mirror {

    /**
     * @var EntityStorageFactory
     */
    private $storageFactory;

    /**
     * @var EntityStorage[]
     */
    private $storages = array();

    function __construct(EntityStorageFactory $storageFactory) {
        $this->storageFactory = $storageFactory;
    }

    public function save($entityType, $data, $restriction = array()) {
        $storage = $this->getStorage($entityType);
        if($storage == null)
            return;
        $storage->save($data, $restriction);
    }

    public function delete($entityType, $restriction) {
        $storage = $this->getStorage($entityType);
        if($storage == null)
            return;
        $storage->delete($restriction);
    }

    /**
     * @param string $entityType
     * @return EntityStorage
     */
    private function getStorage($entityType) {
        if(isset($this->storages[$entityType])) {
            return $this->storages[$entityType];
        }

        $storage = $this->storageFactory->getStorage($entityType);

        if($storage != null) {
            $this->storages[$entityType] = $storage;
        }

        return $storage;
    }
}