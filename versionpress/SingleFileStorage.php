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

    protected $savedFields = array();

    function __construct($file, $entityName, $idColumnName) {
        $this->file = $file;
        $this->idColumnName = $idColumnName;
        $this->entityName = $entityName;
    }

    function save($data, $restriction = array(), $id = 0) {
        if (!isset($data[$this->idColumnName]))
            $data[$this->idColumnName] = $restriction[$this->idColumnName];
        $this->saveEntity($data, array($this, 'notifyOnChangeListeners'));
    }

    function delete($restriction) {
        if(!$this->shouldBeSaved($restriction))
            return;

        $id = $restriction[$this->idColumnName];

        $this->loadEntities();
        unset($this->entities[$id]);
        $this->saveEntities();
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

    private function saveEntity($data, $callback = null) {
        if(!$this->shouldBeSaved($data))
            return;

        $id = $data[$this->idColumnName];

        $this->loadEntities();
        $isNew = !isset($this->entities[$id]);

        if($isNew) {
            $this->entities[$id] = array();
        }
        $originalEntities = $this->entities;

        $this->updateEntity($id, $data);

        if($this->entities != $originalEntities) {
            $this->saveEntities();

            if (is_callable($callback))
            $callback($id, $isNew);
        }
    }

    protected function updateEntity($id, $data) {
        $originalValues = $this->entities[$id];

        foreach($this->savedFields as $field)
            $this->entities[$id][$field] = isset($data[$field]) ? $data[$field] : $originalValues[$field];
    }

    private function loadEntities() {
        if (is_file($this->file))
            $this->entities = parse_ini_file($this->file, true);
        else
            $this->entities = array();
    }

    private function saveEntities() {
        $options = IniSerializer::serialize($this->entities);
        file_put_contents($this->file, $options);
    }

    private function notifyOnChangeListeners($optionName, $isNewOption) {
        $changeInfo = new ChangeInfo();
        $changeInfo->entityType = $this->entityName;
        $changeInfo->entityId = $optionName;
        $changeInfo->type = $isNewOption ? 'create' : 'edit';

        $this->callOnChangeListeners($changeInfo);
    }

    protected function shouldBeSaved(array $data) {
        return true;
    }
}