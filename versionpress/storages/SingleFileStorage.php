<?php

abstract class SingleFileStorage extends ObservableStorage implements EntityStorage {

    protected $entities;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var string
     */
    protected $idColumnName;

    /**
     * @var string
     */
    protected $entityName;

    protected $notSavedFields = array();

    function __construct($file, $entityName, $idColumnName) {
        $this->file = $file;
        $this->idColumnName = $idColumnName;
        $this->entityName = $entityName;
        $this->notSavedFields[] = $idColumnName;
    }

    function save($data, $restriction = array(), $id = 0) {
        if (!isset($data[$this->idColumnName])) {
            if (isset($restriction[$this->idColumnName]))
                $data[$this->idColumnName] = $restriction[$this->idColumnName];
            else
                $data[$this->idColumnName] = $id;
        }

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

    protected function saveEntity($data, $callback = null) {
        if (!$this->shouldBeSaved($data))
            return;

        $id = $data[$this->idColumnName];

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
                $callback($id, $isNew ? 'create' : 'edit');
        }
    }

    protected function updateEntity($id, $data) {

        foreach ($this->notSavedFields as $field)
            unset($data[$field]);

        foreach($data as $field => $value)
            $this->entities[$id][$field] = $value;

    }

    protected function loadEntities() {
        if (is_file($this->file))
            $this->entities = IniSerializer::deserialize(file_get_contents($this->file));
        else
            $this->entities = array();
    }

    protected function saveEntities() {
        $entities = IniSerializer::serialize($this->entities);
        file_put_contents($this->file, $entities);
    }

    protected function notifyOnChangeListeners($entityId, $changeType) {
        $changeInfo = new ChangeInfo();
        $changeInfo->entityType = $this->entityName;
        $changeInfo->entityId = $entityId;
        $changeInfo->type = $changeType;

        $this->callOnChangeListeners($changeInfo);
    }

    public function shouldBeSaved($data) {
        return true;
    }
}