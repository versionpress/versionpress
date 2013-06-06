<?php

class Mirror {
    /**
     * @var EntityStorageFactory
     */
    private $storageFactory;

    /**
     * @var array
     */
    private $registeredStorages = array();

    /**
     * @var bool
     */
    private $wasAffected;

    private $changeList;

    function __construct(EntityStorageFactory $storageFactory) {
        $this->storageFactory = $storageFactory;
    }

    public function save($entityType, $data, $restriction = array(), $insertId = 0) {
        $storage = $this->getStorage($entityType);
        if ($storage == null)
            return;
        $storage->save($data, $restriction, $insertId);
    }

    public function delete($entityType, $restriction) {
        $storage = $this->getStorage($entityType);
        if ($storage == null)
            return;
        $storage->delete($restriction);
    }

    public function wasAffected() {
        return $this->wasAffected;
    }

    public function getChangeList() {
        return $this->changeList;
    }

    /**
     * @param string $entityType
     * @return EntityStorage
     */
    private function getStorage($entityType) {

        $storage = $this->storageFactory->getStorage($entityType);
        if($storage == null)
            return null;

        $object_hash = spl_object_hash($storage);

        if ($storage != null && !isset($this->registeredStorages[$object_hash])) {
            $this->registeredStorages[$object_hash] = true;

            $that = $this;
            $storage->addChangeListener(function (ChangeInfo $changeInfo) use ($that) {
                $that->wasAffected = true;
                $this->changeList[] = $changeInfo;
            });
        }

        return $storage;
    }

    public function shouldBeSaved($entityName, $data) {
        $storage = $this->getStorage($entityName);
        if($storage === null)
            return false;
        return $storage->shouldBeSaved($data);
    }
}