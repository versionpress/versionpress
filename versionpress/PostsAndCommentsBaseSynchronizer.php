<?php

abstract class PostsAndCommentsBaseSynchronizer {

    /**
     * @var EntityStorage
     */
    protected $storage;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var wpdb
     */
    protected $database;

    /**
     * @var string
     */
    protected $idColumnName;

    /**
     * @var string
     */
    protected $parentIdColumnName;

    function __construct(EntityStorage $storage, wpdb $database, $tableName, $idColumnName, $parentIdColumnName) {
        $this->storage = $storage;
        $this->database = $database;
        $this->tableName = $tableName;
        $this->idColumnName = $idColumnName;
        $this->parentIdColumnName = $parentIdColumnName;
    }

    function synchronize() {
        $this->updateDatabase();
        $this->fixParentIds();
        $this->mirrorDatabaseToStorage();
    }

    protected function doAfterDatabaseUpdate() {

    }

    private function updateDatabase() {
        $posts = $this->loadAllEntitiesFromStorage();
        $postWithoutIDs = array_map(function($post){ unset($post[$this->idColumnName]); return $post; }, $posts);

        foreach ($postWithoutIDs as $post) {
            $sql = $this->buildInsertWithUpdateFallbackQuery($post);
            $this->database->query($sql);
        }

        $postVpIds = array_map(function($post){ return $post['vp_id'];  }, $posts);
        $sql = "DELETE FROM {$this->tableName} WHERE vp_id NOT IN (" . implode(', ', $postVpIds) . ")";
        $this->database->query($sql);

        $this->doAfterDatabaseUpdate();
    }

    private function loadAllEntitiesFromStorage() {
        $posts = $this->storage->loadAll();
        return $posts;
    }

    private function buildInsertWithUpdateFallbackQuery($data) {
        $columns = array_keys($data);
        $stringColumns = implode(', ', $columns);
        $safeValues = array_map(function($value){ return "\"$value\""; }, $data);
        $stringValues = implode(', ', $safeValues);
        $updatePairs = array_map(function($column) use ($safeValues){ return "$column = $safeValues[$column]"; }, $columns);
        $updateString = implode(', ', $updatePairs);

        $sql = "INSERT INTO {$this->tableName} ($stringColumns) VALUES ($stringValues)
                ON DUPLICATE KEY UPDATE $updateString";

        return $sql;
    }

    private function fixParentIds() {
        $sql = "SELECT {$this->idColumnName}, post_parent, vp_id, vp_parent_id FROM {$this->tableName}";
        $entities = $this->database->get_results($sql);
        $vpId_ID_map = array();
        foreach($entities as $entity) {
            $vpId_ID_map[$entity->vp_id] = $entity->{$this->idColumnName};
        }

        foreach($entities as $entity) {
            $newParent = 0;
            if($entity->vp_parent_id != 0){
                $newParent = $vpId_ID_map[$entity->vp_parent_id];
            }
            if($entity->{$this->parentIdColumnName} != $newParent) {
                $updateSql = "UPDATE {$this->tableName} SET {$this->parentIdColumnName} = $newParent WHERE {$this->idColumnName} = " . $entity->{$this->idColumnName};
                $this->database->query($updateSql);
            }
        }
    }

    private function mirrorDatabaseToStorage() {
        $entitiesInDatabase = $this->loadAllEntitiesFromDatabase();
        $entitiesInStorage = $this->loadAllEntitiesFromStorage();

        $getEntityId = function($entity){ return $entity[$this->idColumnName]; };

        $dbEntityIds = array_map($getEntityId, $entitiesInDatabase);
        $storageEntityIds = array_map($getEntityId, $entitiesInStorage);

        $entitiesToDelete =  array_diff($storageEntityIds, $dbEntityIds);

        foreach($entitiesToDelete as $entityId) {
            $this->storage->delete(array($this->idColumnName => $entityId));
        }

        $this->storage->saveAll($entitiesInDatabase);
    }

    private function loadAllEntitiesFromDatabase() {
        $sql = "SELECT * FROM {$this->tableName}";
        return $this->database->get_results($sql, ARRAY_A);
    }
}