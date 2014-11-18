<?php

/**
 * Reflects database changes to storages. It is a facade / entry point to a set
 * of storages {@link Storage storages} that implement the actual functionality.
 */
class Mirror {

    /** @var StorageFactory */
    private $storageFactory;

    /** @var bool */
    private $wasAffected;

    /** @var ChangeInfo[] */
    private $changeList;

    function __construct(StorageFactory $storageFactory) {
        $this->storageFactory = $storageFactory;
    }

    /**
     * Chooses an appropriate storage and calls its {@see Storage::save() save()} method.
     *
     * @see Storage::save()
     *
     * @param string $entityType Entity type determines the storage used
     * @param array $data Data passed to the `Storage::save()` method
     */
    public function save($entityType, $data) {
        $storage = $this->storageFactory->getStorage($entityType);
        if ($storage == null) {
            return;
        }

        $changeInfo = $storage->save($data);
        if ($changeInfo) {
            $this->changeList[] = $changeInfo;
        }
    }

    /**
     * Chooses an appropriate storage and calls its {@see Storage::delete() delete()} method.
     *
     * @see Storage::delete()
     *
     * @param string $entityType Entity type determines the storage used
     * @param array $restriction Restriction passed to the `Storage::delete()` method
     */
    public function delete($entityType, $restriction) {
        $storage = $this->storageFactory->getStorage($entityType);
        if ($storage == null) {
            return;
        }

        $changeInfo = $storage->delete($restriction);
        if ($changeInfo) {
            $this->changeList[] = $changeInfo;
        }
    }

    /**
     * True if at least one of the calls to `save()` actually influenced the storage somehow.
     *
     * @return bool
     */
    public function wasAffected() {
        return $this->wasAffected;
    }

    /**
     * If wasAffected(), this array contains a list of {@see ChangeInfo} objects
     *
     * @return ChangeInfo[]
     */
    public function getChangeList() {
        return $this->changeList;
    }

    /**
     * Queries the associated storage whether the entity data should be saved or not
     *
     * @see Storage::shouldBeSaved()
     *
     * @param string $entityType Determines the storage
     * @param array $data Data passed to Storage::shouldBeSaved()
     * @return bool
     */
    public function shouldBeSaved($entityType, $data) {
        $storage = $this->storageFactory->getStorage($entityType);
        if($storage === null)
            return false;
        return $storage->shouldBeSaved($data);
    }

}