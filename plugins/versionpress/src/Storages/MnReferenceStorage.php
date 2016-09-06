<?php

namespace VersionPress\Storages;

class MnReferenceStorage extends Storage
{
    private $parentStorage;
    private $referenceDetails;
    private $parentEntity;
    private $targetEntity;

    /**
     * @param Storage $parentStorage
     * @param array $referenceDetails
     */
    public function __construct($parentStorage, $referenceDetails)
    {
        $this->parentStorage = $parentStorage;
        $this->referenceDetails = $referenceDetails;
        $this->parentEntity = $referenceDetails['source-entity'];
        $this->targetEntity = $referenceDetails['target-entity'];
    }

    public function save($data)
    {
        $parentEntityField = "vp_{$this->parentEntity}";
        $targetEntityField = "vp_{$this->targetEntity}";

        $parentVpid = $data[$parentEntityField];
        $targetVpid = $data[$targetEntityField];

        $parentEntity = $this->parentStorage->loadEntity($parentVpid, null);

        $references = isset($parentEntity[$targetEntityField]) ? $parentEntity[$targetEntityField] : [];

        if (array_search($targetVpid, $references) !== false) {
            return null;
        }

        $references[] = $targetVpid;
        $parentEntity[$targetEntityField] = $references;
        return $this->parentStorage->save($parentEntity);
    }

    public function delete($restriction)
    {
        $parentEntityField = "vp_{$this->parentEntity}";
        $targetEntityField = "vp_{$this->targetEntity}";

        $parentVpid = $restriction[$parentEntityField];
        $targetVpid = $restriction[$targetEntityField];

        $parentEntity = $this->parentStorage->loadEntity($parentVpid, null);

        $references = isset($parentEntity[$targetEntityField]) ? $parentEntity[$targetEntityField] : [];

        $index = array_search($targetVpid, $references);
        if ($index === false) {
            return null;
        }

        $references = array_merge(array_slice($references, 0, $index), array_slice($references, $index + 1));

        $parentEntity[$targetEntityField] = $references;
        return $this->parentStorage->save($parentEntity);
    }

    public function loadEntity($id, $parentId)
    {
        throw new \Exception('M:N reference cannot be identified by ID; thus, it cannot be loaded.');
    }

    public function loadAll()
    {
        throw new \Exception('M:N reference cannot be identified by ID; thus, it cannot be loaded.');
    }

    public function prepareStorage()
    {
        $this->parentStorage->prepareStorage();
    }

    public function getEntityFilename($id, $parentId)
    {
        return $this->parentStorage->getEntityFilename($parentId, null);
    }

    public function getPathCommonToAllEntities()
    {
        return $this->parentStorage->getPathCommonToAllEntities();
    }

    public function exists($id, $parentId)
    {
        throw new \Exception('M:N reference cannot be identified by ID; thus, its existence cannot be checked.');
    }

    public function saveLater($data)
    {
        $parentEntityField = "vp_{$this->parentEntity}";
        $targetEntityField = "vp_{$this->targetEntity}";

        $parentVpid = $data[$parentEntityField];
        $targetVpid = $data[$targetEntityField];

        $parentEntity = $this->parentStorage->loadEntity($parentVpid, null);

        $references = isset($parentEntity[$targetEntityField]) ? $parentEntity[$targetEntityField] : [];

        if (array_search($targetVpid, $references) !== false) {
            return null;
        }

        $references[] = $targetVpid;
        $parentEntity[$targetEntityField] = $references;
        return $this->parentStorage->saveLater($parentEntity);
    }

    public function commit()
    {
        $this->parentStorage->commit();
    }
}
