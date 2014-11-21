<?php

/**
 * Saves entities to files in a common directory. Useful for entities that either
 * expect a lot of instance of them (posts, comments etc.) or have variable length
 * and some may be rather large (again, e.g. posts).
 *
 * For example, posts are stored as <vpid>.ini in the `vpdb/posts` folder.
 *
 * Note that the same file can be used by multiple entities. For example, both
 * the main post data and postmeta for it are stored in the same INI file.
 */
abstract class DirectoryStorage extends Storage {

    /** @var string */
    private $directory;

    /** @var EntityFilter[] */
    private $filters = array();

    private $entityInfo;

    function __construct($directory, $entityInfo) {
        $this->directory = $directory;
        $this->entityInfo = $entityInfo;
    }

    function save($data) {
        $id = $data[$this->entityInfo->vpidColumnName];

        if (!$id) {
            return null;
        }

        if ($this->entityInfo->usesGeneratedVpids) {
            // to avoid merge conflicts
            unset($data[$this->entityInfo->idColumnName]);
        }
        $data = $this->removeUnwantedColumns($data);
        $data = $this->applyFilters($data);

        if (!$this->shouldBeSaved($data)) {
            return null;
        }

        $filename = $this->getEntityFilename($id);
        $oldSerializedEntity = "";
        $isExistingEntity = $this->isExistingEntity($id);

        if ($isExistingEntity) {
            $oldSerializedEntity = file_get_contents($filename);
        }

        $oldEntity = $this->deserializeEntity($oldSerializedEntity);
        $diff = EntityUtils::getDiff($oldEntity, $data);

        if (count($diff) > 0) {

            $newEntity = array_merge($oldEntity, $diff);
            file_put_contents($filename, $this->serializeEntity($newEntity));

            return $this->createChangeInfo($oldEntity, $newEntity, !$isExistingEntity ? 'create' : null);

        } else {
            return null;
        }

    }

    function delete($restriction) {
        $fileName = $this->getEntityFilename($restriction['vp_id']);
        if (is_file($fileName)) {
            $entity = $this->loadEntity($restriction['vp_id']);
            unlink($fileName);
            return $this->createChangeInfo($entity, $entity, 'delete');
        } else {
            return null;
        }
    }

    function loadAll() {
        $entityFiles = $this->getEntityFiles();
        $entities = $this->loadAllFromFiles($entityFiles);
        return $entities;
    }

    function saveAll($entities) {
        foreach ($entities as $entity) {
            $this->save($entity);
        }
    }

    public function shouldBeSaved($data) {
        return true;
    }

    public function prepareStorage() {
        @mkdir($this->directory, 0777, true);
    }

    public function getEntityFilename($id) {
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
        return array_map(function ($filename) use ($directory) {
            return $directory . '/' . $filename;
        }, array_diff($files, $excludeList));
    }

    private function loadAllFromFiles($entityFiles) {
        $entities = array();
        $indexedEntities = array();

        foreach ($entityFiles as $file) {
            $entities[] = $this->deserializeEntity(file_get_contents($file));
        }

        foreach ($entities as $entity) {
            $indexedEntities[$entity['vp_id']] = $entity;
        }

        return $indexedEntities;
    }

    protected function removeUnwantedColumns($entity) {
        return $entity;
    }

    protected function isExistingEntity($id) {
        return file_exists($this->getEntityFilename($id));
    }

    private function loadEntity($vpid) {
        $entities = $this->loadAllFromFiles(array($this->getEntityFilename($vpid)));
        return $entities[$vpid];
    }

    protected function applyFilters($data) {
        foreach ($this->filters as $filter) {
            $data = $filter->apply($data);
        }

        return $data;
    }

    protected function addFilter(EntityFilter $filter) {
        $this->filters[] = $filter;
    }
}