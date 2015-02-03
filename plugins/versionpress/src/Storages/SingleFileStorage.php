<?php

namespace VersionPress\Storages;
use VersionPress\Utils\IniSerializer;

/**
 * Saves entities of same type to a single file. Useful for entities for which
 * there aren't expected to exist many instances, or where the length
 * of each entity is relatively fixed. For example, users or options use this storage.
 */
abstract class SingleFileStorage extends Storage {


    protected $file;
    protected $entityInfo;

    /**
     * All entities from this storage. Available after loadEntities() has been called.
     *
     * @var array
     */
    protected $entities;

    /**
     * Array of fields that should be ignored / not saved in storage
     *
     * @var array
     */
    protected $notSavedFields = array();

    function __construct($file, $entityInfo) {
        $this->file = $file;
        $this->entityInfo = $entityInfo;
    }

    function save($data) {
        if (!$this->shouldBeSaved($data))
            return null;

        $vpid = $data[$this->entityInfo->vpidColumnName];

        if (!$vpid) {
            return null;
        }

        $this->loadEntities();
        $originalEntities = $this->entities;

        $isNew = !isset($this->entities[$vpid]);

        if ($isNew) {
            $this->entities[$vpid] = array();
        }

        $this->updateEntity($vpid, $data);

        if ($this->entities != $originalEntities) {
            $this->saveEntities();
            return $this->createChangeInfo(null, $this->entities[$vpid], $isNew ? 'create' : 'edit');
        } else {
            return null;
        }

    }

    function delete($restriction) {
        if (!$this->shouldBeSaved($restriction)) {
            return null;
        }

        $vpid = $restriction[$this->entityInfo->vpidColumnName];

        $this->loadEntities();
        $originalEntities = $this->entities;
        $entity = $this->entities[$vpid];

        unset($this->entities[$vpid]);

        if ($this->entities != $originalEntities) {
            $this->saveEntities();
            return $this->createChangeInfo(null, $entity, 'delete');
        } else {
            return null;
        }
    }

    function loadEntity($id) {
        $this->loadEntities();
        return $this->entities[$id];
    }

    function loadAll() {
        $this->loadEntities();
        return $this->entities;
    }

    function saveAll($entities) {
        foreach ($entities as $entity) {
            $this->save($entity);
        }
    }

    function prepareStorage() {
    }

    /**
     * Updates entity on index $id with values in $data. Fields listed in $this->notSavedFields
     * are ignored.
     *
     * @param string $id
     * @param array $data key => value
     */
    private function updateEntity($id, $data) {

        foreach ($this->notSavedFields as $field) {
            unset($data[$field]);
        }

        foreach ($data as $field => $value) {
            $this->entities[$id][$field] = $value;
        }

    }

    /**
     * Loads all entities from a file to the $this->entities if they were not already loaded
     */
    protected function loadEntities() {
        if ($this->entities) {
            return;
        }

        if (is_file($this->file)) {
            $entities = IniSerializer::deserialize(file_get_contents($this->file));
            $this->entities = $entities;
        } else {
            $this->entities = array();
        }
    }

    /**
     * Saves all entities to a file
     */
    protected function saveEntities() {
        $entities = IniSerializer::serializeSectionedData($this->entities);
        file_put_contents($this->file, $entities);
    }

    public function shouldBeSaved($data) {
        return true;
    }

    function getEntityFilename($id) {
        return $this->file;
    }

}
