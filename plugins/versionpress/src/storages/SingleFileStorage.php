<?php

/**
 * Saves entities of same type to a single file. Useful for entities for which
 * there aren't expected to exist many instances, or where the length
 * of each entity is relatively fixed. For example, users or options use this storage.
 */
abstract class SingleFileStorage extends Storage {

    protected $entities;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var string
     */
    protected $idColumnName = 'vp_id';

    /**
     * @var string
     */
    protected $entityTypeName;

    protected $notSavedFields = array();

    function __construct($file, $entityTypeName, $idColumnName) {
        $this->file = $file;
        $this->entityTypeName = $entityTypeName;
    }

    function save($data) {
        if (!$this->shouldBeSaved($data))
            return null;

        $id = $data[$this->idColumnName];

        if (!$id)
            return null;

        $this->loadEntities();
        $isNew = !isset($this->entities[$id]);

        if ($isNew) {
            $this->entities[$id] = array();
        }
        $originalEntities = $this->entities;

        $this->updateEntity($id, $data);

        if ($this->entities != $originalEntities) {
            $this->saveEntities();
            return $this->createChangeInfo(null, $this->entities[$id], $isNew ? 'create' : 'edit');
        } else {
            return null;
        }

    }

    function delete($restriction) {
        if (!$this->shouldBeSaved($restriction))
            return null;

        $id = $restriction[$this->idColumnName];

        $this->loadEntities();
        $originalEntities = $this->entities;
        $entity = $this->entities[$id];
        unset($this->entities[$id]);
        if ($this->entities != $originalEntities) {
            $this->saveEntities();
            return $this->createChangeInfo(null, $entity, 'delete');
        } else {
            return null;
        }
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

    protected function updateEntity($id, $data) {

        foreach ($this->notSavedFields as $field)
            unset($data[$field]);

        foreach ($data as $field => $value)
            $this->entities[$id][$field] = $value;

    }

    protected function loadEntities() {
        if($this->entities) return;

        if (is_file($this->file)){
            $entities = IniSerializer::deserialize(file_get_contents($this->file));
            $this->entities = $entities;
        }
        else
            $this->entities = array();
    }

    protected function saveEntities() {
        $entities = IniSerializer::serialize($this->entities);
        file_put_contents($this->file, $entities);
    }

    public function shouldBeSaved($data) {
        return true;
    }

    function getEntityFilename($id) {
        return $this->file;
    }

}