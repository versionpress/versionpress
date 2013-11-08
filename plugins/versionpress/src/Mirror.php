<?php

/**
 * Mirror reflects all DB changes to storages
 */
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
    public $wasAffected;

    public $changeList;

    function __construct(EntityStorageFactory $storageFactory) {
        $this->storageFactory = $storageFactory;
    }

    public function save($entityType, $data) {
        $storage = $this->getStorage($entityType);
        if ($storage == null)
            return;
        $storage->save($data);
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
                $that->changeList[] = $changeInfo;
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