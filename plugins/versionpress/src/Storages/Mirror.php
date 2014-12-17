<?php
namespace VersionPress\Storages;

use VersionPress\ChangeInfos\TrackedChangeInfo;

/**
 * Reflects database changes to storages. It is a facade / entry point to a set
 * of storages {@link VersionPress\Storages\Storage storages} that implement the actual functionality.
 */
class Mirror {

    /** @var StorageFactory */
    private $storageFactory;

    /** @var TrackedChangeInfo[] */
    private $changeList = array();

    function __construct(StorageFactory $storageFactory) {
        $this->storageFactory = $storageFactory;
    }

    /**
     * Chooses an appropriate storage and calls its {@see VersionPress\Storages\Storage::save() save()} method.
     *
     * @see Storage::save()
     *
     * @param string $entityName Entity type determines the storage used
     * @param array $data Data passed to the `VersionPress\Storages\Storage::save()` method
     */
    public function save($entityName, $data) {
        $storage = $this->storageFactory->getStorage($entityName);
        if ($storage == null) {
            return;
        }

        $changeInfo = $storage->save($data);
        if ($changeInfo) {
            $this->changeList[] = $changeInfo;
        }
    }

    /**
     * Chooses an appropriate storage and calls its {@see VersionPress\Storages\Storage::delete() delete()} method.
     *
     * @see Storage::delete()
     *
     * @param string $entityName Entity type determines the storage used
     * @param array $restriction Restriction passed to the `VersionPress\Storages\Storage::delete()` method
     */
    public function delete($entityName, $restriction) {
        $storage = $this->storageFactory->getStorage($entityName);
        if ($storage == null) {
            return;
        }

        $changeInfo = $storage->delete($restriction);
        if ($changeInfo) {
            $this->changeList[] = $changeInfo;
        }
    }

    /**
     * Contains a list of {@see VersionPress\ChangeInfos\ChangeInfo} objects captured by this mirror.
     * Can be an empty array if there was no real change in any of the storages.
     *
     * @return TrackedChangeInfo[]
     */
    public function getChangeList() {
        return $this->changeList;
    }

    /**
     * Queries the associated storage whether the entity data should be saved or not
     *
     * @see Storage::shouldBeSaved()
     *
     * @param string $entityName Determines the storage
     * @param array $data Data passed to VersionPress\Storages\Storage::shouldBeSaved()
     * @return bool
     */
    public function shouldBeSaved($entityName, $data) {
        $storage = $this->storageFactory->getStorage($entityName);
        if ($storage === null)
            return false;
        return $storage->shouldBeSaved($data);
    }

}
