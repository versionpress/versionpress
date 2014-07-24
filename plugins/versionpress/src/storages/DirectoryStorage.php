<?php

/**
 * Saves entities into separate files in given directory
 */
abstract class DirectoryStorage extends ObservableStorage implements EntityStorage {
    /**
     * @var string
     */
    private $directory;

    protected $entityTypeName;

    protected $idColumnName;

    function __construct($directory, $entityTypeName, $idColumnName = 'ID') {
        $this->directory = $directory;
        $this->entityTypeName = $entityTypeName;
        $this->idColumnName = $idColumnName;
    }

    function save($data) {
        $this->saveEntity($data, array($this, 'notifyChangeListeners'));
    }

    function delete($restriction) {
        $fileName = $this->getFilename($restriction['vp_id']);
        if (is_file($fileName)) {
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

    public function shouldBeSaved($data) {
        return true;
    }

    function prepareStorage() {
        @mkdir($this->directory, 0777, true);
    }

    private function getFilename($id) {
        return $this->directory . '/' . $id . '.ini';
    }

    private function deserializeEntity($serializedEntity) {
        return IniSerializer::deserialize($serializedEntity);
    }

    private function serializeEntity($entity) {
        return IniSerializer::serializeFlatData($entity);
    }

    private function getEntityFiles() {
        if (!is_dir($this->directory))
            return array();
        $excludeList = array('.', '..');
        $files = scandir($this->directory);

        $directory = $this->directory;
        return array_map(function($filename) use ($directory) { return $directory . '/' . $filename; }, array_diff($files, $excludeList));
    }

    private function loadAllFromFiles($entityFiles) {
        $that = $this;
        return array_map(function ($entityFile) use ($that) {
            return $that->deserializeEntity(file_get_contents($entityFile));
        }, $entityFiles);
    }

    protected function removeUnwantedColumns($entity) {
        return $entity;
    }

    private function notifyChangeListeners($entity, $changeType) {
        $changeInfo = $this->createChangeInfo($entity, $changeType);
        $this->callOnChangeListeners($changeInfo);
    }

    protected abstract function createChangeInfo($entity, $changeType);

    private function saveEntity($data, $callback = null) {
        $id = $data['vp_id'];

        if (!$id)
            return;

        $data = $this->removeUnwantedColumns($data);

        $filename = $this->getFilename($id);
        $oldSerializedEntity = "";
        $isExistingEntity = $this->isExistingEntity($id);

        if (!$this->shouldBeSaved($data))
            return;

        if ($isExistingEntity) {
            $oldSerializedEntity = file_get_contents($filename);
        }

        $entity = $this->deserializeEntity($oldSerializedEntity);
        if (isset($entity['vp_id']))
            unset($data['vp_id']);

        $diffData = array();
        foreach ($data as $key => $value) {
            if (!isset($entity[$key]) || (isset($entity[$key]) && $entity[$key] != $value)) // not present or different value
                $diffData[$key] = $value;
        }


        if (count($diffData) > 0) {
            $entity = array_merge($entity, $diffData);
            file_put_contents($filename, $this->serializeEntity($entity));
            if (is_callable($callback))
                call_user_func($callback, $entity, $isExistingEntity ? $this->getEditAction($diffData) : 'create');
        }
    }

    protected function isExistingEntity($id) {
        return file_exists($this->getFilename($id));
    }

    /**
     * @return string
     */
    protected function getEditAction($diffData) {
        return 'edit';
    }

    private function loadEntity($id) {
        $entities = $this->loadAllFromFiles(array($this->getFilename($id)));
        return $entities[$id];
    }
}