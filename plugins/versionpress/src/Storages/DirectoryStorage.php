<?php
namespace VersionPress\Storages;

use Nette\Utils\Strings;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use VersionPress\Utils\ArrayUtils;
use VersionPress\Utils\EntityUtils;
use VersionPress\Utils\FileSystem;
use VersionPress\Storages\Serialization\IniSerializer;

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

    private $uncommittedEntities = array();

    public function __construct($directory, $entityInfo) {
        parent::__construct($entityInfo);
        $this->directory = $directory;
        $this->entityInfo = $entityInfo;
    }

    public function save($data) {
        $vpid = $data[$this->entityInfo->vpidColumnName];

        if (!$vpid) {
            return null;
        }

        if ($this->entityInfo->usesGeneratedVpids) {
            // to avoid merge conflicts
            unset($data[$this->entityInfo->idColumnName]);
        }
        $data = $this->removeUnwantedColumns($data);

        if (!$this->shouldBeSaved($data)) {
            return null;
        }

        $filename = $this->getEntityFilename($vpid);
        $oldSerializedEntity = "";
        $isExistingEntity = $this->exists($vpid);

        if ($isExistingEntity) {
            $oldSerializedEntity = file_get_contents($filename);
        }

        $oldEntity = $this->deserializeEntity($oldSerializedEntity);
        $diff = EntityUtils::getDiff($oldEntity, $data);

        if (count($diff) > 0) {
            $newEntity = array_merge($oldEntity, $diff);
            $newEntity = array_filter($newEntity, function ($value) { return $value !== false; });

            FileSystem::mkdir(dirname($this->getEntityFilename($vpid)));
            file_put_contents($filename, $this->serializeEntity($vpid, $newEntity));

            return $this->createChangeInfo($oldEntity, $newEntity, !$isExistingEntity ? 'create' : 'edit');

        } else {
            return null;
        }

    }

    public function delete($restriction) {
        $fileName = $this->getEntityFilename($restriction[$this->entityInfo->vpidColumnName]);
        if (is_file($fileName)) {
            $entity = $this->loadEntity($restriction[$this->entityInfo->vpidColumnName]);
            FileSystem::remove($fileName);
            return $this->createChangeInfo($entity, $entity, 'delete');
        } else {
            return null;
        }
    }

    public function saveLater($data) {
        $vpid = $data[$this->entityInfo->vpidColumnName];

        if (!isset($this->uncommittedEntities[$vpid])) {
            $this->uncommittedEntities[$vpid] = array();
        }

        $originalEntity = $this->uncommittedEntities[$vpid];
        $newEntity = array_merge($originalEntity, $data);
        $this->uncommittedEntities[$vpid] = $newEntity;
    }

    public function commit() {
        foreach ($this->uncommittedEntities as $entity) {
            $this->save($entity);
        }

        $this->uncommittedEntities = array();
    }

    public function loadAll() {
        $entityFiles = $this->getEntityFiles();
        $entities = $this->loadAllFromFiles($entityFiles);
        return $entities;
    }

    public function prepareStorage() {
        FileSystem::mkdir($this->directory);
    }

    public function getEntityFilename($id, $parentId = null) {
        $vpidPath = Strings::substring($id, 0, 2) . '/' . $id;
        return $this->directory . '/' . $vpidPath . '.ini';
    }

    public function getPathCommonToAllEntities() {
        return $this->directory;
    }

    protected function deserializeEntity($serializedEntity) {
        $entity = IniSerializer::deserialize($serializedEntity);
        return $this->flattenEntity($entity);
    }

    protected function serializeEntity($vpid, $entity) {
        unset ($entity[$this->entityInfo->vpidColumnName]);
        return IniSerializer::serialize(array($vpid => $entity));
    }

    private function getEntityFiles() {
        if (!is_dir($this->directory))
            return array();

        $directoryIterator = new RecursiveDirectoryIterator($this->directory);
        $recursiveIterator = new RecursiveIteratorIterator($directoryIterator);
        $iniFilesIterator = new RegexIterator($recursiveIterator, '~^.+\.ini$~i', RecursiveRegexIterator::GET_MATCH);

        return array_keys(iterator_to_array($iniFilesIterator));
    }

    private function loadAllFromFiles($entityFiles) {
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        $entities = array_map(array($this, 'deserializeEntity'), array_filter(@array_map('file_get_contents', $entityFiles), function ($item) { return $item !== FALSE; }));
        $vpIds = ArrayUtils::column($entities, $this->entityInfo->vpidColumnName);
        return array_combine($vpIds, $entities);
    }

    protected function removeUnwantedColumns($entity) {
        return $entity;
    }

    public function exists($id, $parentId = null) {
        return file_exists($this->getEntityFilename($id));
    }

    public function loadEntity($id, $parentId = null) {
        $entities = $this->loadAllFromFiles(array($this->getEntityFilename($id)));
        return isset($entities[$id]) ? $entities[$id] : FALSE;
    }

    protected function flattenEntity($entity) {
        if (count($entity) === 0) {
            return $entity;
        }

        reset($entity);
        $vpid = key($entity);
        $flatEntity = $entity[$vpid];
        $flatEntity[$this->entityInfo->vpidColumnName] = $vpid;

        return $flatEntity;
    }
}
