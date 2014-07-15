<?php
/**
 * Saves all entities of one type into single file
 */
abstract class SingleFileStorage extends ObservableStorage implements EntityStorage {

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
        $this->saveEntity($data, array($this, 'notifyOnChangeListeners'));
    }

    function delete($restriction) {
        if (!$this->shouldBeSaved($restriction))
            return;

        $id = $restriction[$this->idColumnName];

        $this->loadEntities();
        $originalEntities = $this->entities;
        unset($this->entities[$id]);
        if ($this->entities != $originalEntities) {
            $this->saveEntities();
            $this->notifyOnChangeListeners($id, 'delete');
        }
    }

    function loadAll() {
        $this->loadEntities();
        return $this->entities;
    }

    function saveAll($entities) {
        foreach ($entities as $entity) {
            $this->saveEntity($entity);
        }
    }

    function prepareStorage() {
    }

    protected function saveEntity($data, $callback = null) {
        if (!$this->shouldBeSaved($data))
            return;

        $id = $data[$this->idColumnName];

        if (!$id)
            return;

        $this->loadEntities();
        $isNew = !isset($this->entities[$id]);

        if ($isNew) {
            $this->entities[$id] = array();
        }
        $originalEntities = $this->entities;

        $this->updateEntity($id, $data);

        if ($this->entities != $originalEntities) {
            $this->saveEntities();

            if (is_callable($callback))
                call_user_func($callback, $id, $isNew ? 'create' : 'edit');
        }
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

    protected function notifyOnChangeListeners($entityId, $changeType) {
        $changeInfo = new EntityChangeInfo($this->entityTypeName, $changeType, $entityId);
        $this->callOnChangeListeners($changeInfo);
    }

    public function shouldBeSaved($data) {
        return true;
    }
}