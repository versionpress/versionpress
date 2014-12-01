<?php

/**
 * TODO this needs a better name
 */
abstract class SynchronizerBase implements Synchronizer {

    private $entityName;
    private $idColumnName;

    /** @var Storage */
    private $storage;

    /** @var wpdb */
    private $database;

    /** @var DbSchemaInfo */
    private $dbSchema;

    function __construct(Storage $storage, wpdb $database, DbSchemaInfo $dbSchema, $entityName) {
        $this->storage = $storage;
        $this->database = $database;
        $this->dbSchema = $dbSchema;
        $this->entityName = $entityName;
        $this->idColumnName = $dbSchema->getEntityInfo($this->entityName)->idColumnName;
    }

    function synchronize() {
        $entities = $this->loadEntitiesFromStorage();
        $this->updateDatabase($entities);
        $this->fixReferences($entities);
        $this->doEntitySpecificActions();
    }

    private function updateDatabase($entities) {
        $entities = $this->filterEntities($entities);

        $this->addOrUpdateEntities($entities);
        $this->deleteEntitiesWhichAreNotInStorage($entities);
    }

    private function fixReferences($entities) {

        if (!($this->dbSchema->getEntityInfo($this->entityName)->hasReferences)) {
            return;
        }

        $usedReferences = array();
        foreach ($entities as $entity) {
            $referenceDetails = $this->fixReferencesOfOneEntity($entity);
            $usedReferences = array_merge($usedReferences, $referenceDetails);
        }

        if (count($usedReferences) > 0) { // update reference table
            $referenceTableName = $this->getPrefixedTableName('vp_references');

            $insertQuery = "INSERT INTO {$referenceTableName} (" . join(array_keys($usedReferences[0]), ', ') . ")
            VALUES (" . join(array_map(function ($values) {
                    return join($values, ', ');
                }, $usedReferences), '), (') . ")
            ON DUPLICATE KEY UPDATE reference_vp_id = VALUES(reference_vp_id)";
            $this->executeQuery($insertQuery);

            $constraintOfAllExistingReferences = 'NOT((' . join(array_map(function ($a) {
                    return join(Arrays::parametrize($a), ' AND ');
                }, $usedReferences), ') OR (') . '))';
            $deleteQuery = "DELETE FROM {$referenceTableName} WHERE {$constraintOfAllExistingReferences} AND `table` = \"{$this->entityName}\"";
            $this->executeQuery($deleteQuery);
        }

        $references = $this->dbSchema->getEntityInfo($this->entityName)->references;
        foreach ($references as $referenceName => $_){ // update foreign keys by VersionPress references
            $updateQuery = "UPDATE {$this->getPrefixedTableName($this->entityName)} entity SET `{$referenceName}` =
            IFNULL((SELECT reference_id FROM {$this->getPrefixedTableName('vp_reference_details')} ref
            WHERE ref.id=entity.{$this->idColumnName} AND `table` = \"{$this->entityName}\" and reference = \"{$referenceName}\"), entity.{$referenceName})";
            $this->executeQuery($updateQuery);
        }
    }

    private function updateEntityInDatabase($entity) {
        $updateQuery = $this->buildUpdateQuery($entity);
        $this->executeQuery($updateQuery);
    }

    private function createEntityInDatabase($entity) {
        $createQuery = $this->buildCreateQuery($entity);
        $this->executeQuery($createQuery);
        $id = $this->database->insert_id;
        $this->createIdentifierRecord($entity['vp_id'], $id);
        return $id;
    }

    private function getId($vpId) {
        $vpIdTableName = $this->getPrefixedTableName('vp_id');
        return $this->database->get_var("SELECT id FROM $vpIdTableName WHERE `table` = \"$this->entityName\" AND vp_id = UNHEX('$vpId')");
    }

    private function getPrefixedTableName($tableName) {
        return $this->dbSchema->getPrefixedTableName($tableName);
    }

    protected function buildUpdateQuery($updateData) {
        $id = $updateData['vp_id'];
        $tableName = $this->getPrefixedTableName($this->entityName);
        $query = "UPDATE {$tableName} JOIN (SELECT * FROM {$this->database->prefix}vp_id WHERE `table` = '{$this->entityName}') filtered_vp_id ON {$tableName}.{$this->idColumnName} = filtered_vp_id.id SET";
        foreach ($updateData as $key => $value) {
            if ($key == $this->idColumnName) continue;
            if (NStrings::startsWith($key, 'vp_')) continue;
            $query .= " `$key` = " . (is_numeric($value) ? $value : '"' . mysql_real_escape_string($value) . '"') . ',';
        }
        $query[strlen($query) - 1] = ' '; // strip the last comma
        $query .= " WHERE filtered_vp_id.vp_id = UNHEX('$id')";
        return $query;
    }

    private function isExistingEntity($vpId) {
        return (bool)$this->getId($vpId);
    }

    protected function buildCreateQuery($entity) {
        unset($entity[$this->idColumnName]);
        $columns = array_keys($entity);
        $columns = array_filter($columns, function ($column) {
            return !NStrings::startsWith($column, 'vp_');
        });
        $columnsString = join(', ', array_map(function ($column) {
            return "`$column`";
        }, $columns));

        $query = "INSERT INTO {$this->getPrefixedTableName($this->entityName)} ({$columnsString}) VALUES (";

        foreach ($columns as $column) {
            $query .= (is_numeric($entity[$column]) ? $entity[$column] : '"' . mysql_real_escape_string($entity[$column]) . '"') . ", ";
        }

        $query[strlen($query) - 2] = ' '; // strip the last comma
        $query .= ");";
        return $query;
    }

    /** used for debugging */
    private function executeQuery($query) {
        $result = $this->database->query($query);
        return $result;
    }

    private function createIdentifierRecord($vp_id, $id) {
        $query = "INSERT INTO {$this->getPrefixedTableName('vp_id')} (`table`, vp_id, id)
            VALUES (\"{$this->entityName}\", UNHEX('$vp_id'), $id)";
        $this->executeQuery($query);
    }

    private function deleteEntitiesWhichAreNotInStorage($entities) {
        if (count($entities) == 0)
            return;
        $vpIds = array_map(function ($entity) {
            return 'UNHEX("' . $entity['vp_id'] . '")';
        }, $entities);

        $ids = $this->database->get_col("SELECT id FROM {$this->getPrefixedTableName('vp_id')} " .
        "WHERE `table` = \"{$this->entityName}\" AND vp_id NOT IN (" . join(",", $vpIds) . ")");

        if (count($ids) == 0)
            return;

        $idsString = join(',', $ids);

        $this->executeQuery("DELETE FROM {$this->getPrefixedTableName($this->entityName)} WHERE {$this->idColumnName} IN ({$idsString})");
        $this->executeQuery("DELETE FROM {$this->getPrefixedTableName('vp_id')} WHERE `table` = \"{$this->entityName}\" AND id IN ({$idsString})"); // using cascade delete in mysql
    }

    private function fixReferencesOfOneEntity($entity) {
        $references = $this->getAllReferences($entity);

        $referencesDetails = array();
        foreach ($references as $referenceName => $reference) {
            if ($reference == "")
                continue;

            $referencesDetails[] = array(
                '`table`' => "\"" . $this->entityName . "\"",
                'reference' => "\"" . $referenceName . "\"",
                'vp_id' => 'UNHEX("' . $entity['vp_id'] . '")',
                'reference_vp_id' => 'UNHEX("' . $reference . '")'
            );
        }

        return $referencesDetails;
    }

    private function getAllReferences($entity) {
        $references = array();
        $referencesInfo = $this->dbSchema->getEntityInfo($this->entityName)->references;

        foreach ($entity as $key => $value) {
            if (NStrings::startsWith($key, 'vp_')) {
                $key = NStrings::substring($key, 3);
                if (isset($referencesInfo[$key]))
                    $references[$key] = $value;
            }
        }

        return $references;
    }

    protected function filterEntities($entities) {
        return $entities;
    }

    protected  function transformEntities($entities) {
        return $entities;
    }

    private function loadEntitiesFromStorage() {
        $entities = $this->storage->loadAll();
        $entities = $this->transformEntities($entities);
        return $entities;
    }

    private function addOrUpdateEntities($entities) {
        foreach ($entities as $entity) {
            $vpId = $entity['vp_id'];
            $isExistingEntity = $this->isExistingEntity($vpId);

            if ($isExistingEntity) {
                $this->updateEntityInDatabase($entity);
            } else {
                $this->createEntityInDatabase($entity);
            }
        }
    }

    protected function doEntitySpecificActions() {
    }
}
