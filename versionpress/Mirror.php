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

    /**
     * @var bool
     */
    private $wasAffected;

    private $changeList;

    function __construct(EntityStorageFactory $storageFactory) {
        $this->storageFactory = $storageFactory;
    }

    public function save($entityType, $data, $restriction = array(), $insertId) {
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
        if (isset($this->storages[$entityType])) {
            return $this->storages[$entityType];
        }

        $storage = $this->storageFactory->getStorage($entityType);

        if ($storage != null) {
            $this->storages[$entityType] = $storage;

            $that = $this;
            $storage->addChangeListener(function (ChangeInfo $changeInfo) use ($that) {
                $that->wasAffected = true;
                $this->changeList[] = $changeInfo;
            });
        }

        return $storage;
    }
}