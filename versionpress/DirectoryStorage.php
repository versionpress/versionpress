<?php

abstract class DirectoryStorage implements EntityStorage {
    /**
     * @var string
     */
    private $directory;

    /**
     * @var callable[]
     */
    private $onChangeListeners;

    protected $entityTypeName;

    protected $idColumnName;

    function __construct($directory, $entityTypeName, $idColumnName = 'ID') {
        $this->directory = $directory;
        $this->entityTypeName = $entityTypeName;
        $this->idColumnName = $idColumnName;
    }

    function save($data, $restriction = array()) {
        $this->saveEntity($data, $restriction, array($this, 'notifyChangeListeners'));
    }

    function delete($restriction) {
        $fileName = $this->getFilename(array(), $restriction);
        if(is_file($fileName)){
            unlink($fileName);
            $this->notifyChangeListeners($restriction, 'delete');
        }
    }

    function loadAll() {
        $entityFiles = $this->getEntityFiles();
        $entities = $this->loadAllFromFiles($entityFiles);
        return $entities;
    }

    function saveAll($entities) {
        foreach ($entities as $entity) {
            $this->saveEntity($entity);
        }
    }

    function addChangeListener($callback) {
        $this->onChangeListeners[] = $callback;
    }

    protected function shouldBeSaved($isExistingEntity, $data) {
        return true;
    }

    private function getFilename($data, $restriction) {
        $id = isset($data[$this->idColumnName]) ? $data[$this->idColumnName] : $restriction[$this->idColumnName];
        return $this->directory . '/' . $id . '.txt';
    }

    private function deserializeEntity($serializedEntity) {
        return IniSerializer::deserialize($serializedEntity);
    }

    private function serializeEntity($entity) {
        $entity = $this->removeUnwantedColumns($entity);
        return IniSerializer::serialize($entity);
    }

    private function getEntityFiles() {
        if (!is_dir($this->directory))
            return array();
        $excludeList = array('.', '..');
        $files = scandir($this->directory);

        return array_diff($files, $excludeList);
    }

    private function loadAllFromFiles($entityFiles) {
        $that = $this;
        return array_map(function ($entityFile) use ($that) {
            return $that->deserializeEntity(file_get_contents($that->directory . '/' . $entityFile));
        }, $entityFiles);
    }

    protected function removeUnwantedColumns($entity) {
        return $entity;
    }

    private function notifyChangeListeners($entity, $changeType) {
        $changeInfo = $this->createChangeInfo($entity, $changeType);
        $this->callOnChangeListeners($changeInfo);
    }

    private function createChangeInfo($entity, $changeType) {
        $changeInfo = new ChangeInfo();
        $changeInfo->entityType = $this->entityTypeName;
        $changeInfo->entityId = $entity[$this->idColumnName];
        $changeInfo->type = $changeType;
        return $changeInfo;
    }

    private function callOnChangeListeners(ChangeInfo $changeInfo) {
        foreach ($this->onChangeListeners as $onChangeListener) {
            call_user_func($onChangeListener, $changeInfo);
        }
    }

    private function saveEntity($data, $restriction = array(), $callback = null) {
        $filename = $this->getFilename($data, $restriction);
        $oldSerializedEntity = "";
        $isExistingEntity = file_exists($filename);

        if (!$this->shouldBeSaved($isExistingEntity, $data))
            return;

        if ($isExistingEntity) {
            $oldSerializedEntity = file_get_contents($filename);
        }

        $entity = $this->deserializeEntity($oldSerializedEntity);
        if ($entity != $data) {
            $entity = array_merge($entity, $data);
            file_put_contents($filename, $this->serializeEntity($entity));
            if(is_callable($callback))
                $callback($entity, $isExistingEntity ? 'edit' : 'create');
        }
    }
}