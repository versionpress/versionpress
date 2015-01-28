<?php
namespace VersionPress\Synchronizers;

use Nette\Utils\Strings;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Storages\Storage;
use VersionPress\Utils\ArrayUtils;
use wpdb;

/**
 * Base class for synchronizers that work with generated VPIDs.
 *
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

    /**
     * @param Storage $storage Specific Synchronizers will use specific storage types, see VersionPress\Synchronizers\SynchronizerFactory
     * @param wpdb $database
     * @param DbSchemaInfo $dbSchema
     * @param string $entityName Constructors in subclasses provide this
     */
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


    //--------------------------------------
    // Step 1 - loading entities from storage
    //--------------------------------------

    /**
     * Loads entities from storage and gives subclasses the chance to transform them, see
     * {@see transformEntities()}.
     *
     * @return array
     */
    private function loadEntitiesFromStorage() {
        $entities = $this->storage->loadAll();
        $entities = $this->transformEntities($entities);
        return $entities;
    }

    /**
     * Called after entities have been loaded from storage to give the subclasses
     * a chance to modify the array.
     *
     * @param array $entities Entities as loaded from the storage
     * @return array Entities as transformed (if at all) by this synchronizer
     */
    protected function transformEntities($entities) {
        return $entities;
    }



    //--------------------------------------
    // Step 2 - store entities to db
    //--------------------------------------

    /**
     * Adds, updates and deletes rows in the database
     *
     * @param $entities
     */
    private function updateDatabase($entities) {
        $entities = $this->filterEntities($entities);

        $this->addOrUpdateEntities($entities);
        $this->deleteEntitiesWhichAreNotInStorage($entities);
    }

    /**
     * Subclasses may process the entities before they are stored to the DB.
     * ("Filtering" is not exactly the best term here, may be refactored later.)
     *
     * @param $entities
     * @return mixed
     */
    protected function filterEntities($entities) {
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

    /**
     * True if `vp_id` record is found in the database
     *
     * @param string $vpid
     * @return bool
     */
    private function isExistingEntity($vpid) {
        return (bool)$this->getId($vpid);
    }

    /**
     * Returns a WordPress ID based on the `vp_id` by querying the database
     *
     * @param $vpid
     * @return null|string Null if no mapping for a given `vp_id` is found
     */
    private function getId($vpid) {
        $vpIdTableName = $this->getPrefixedTableName('vp_id');
        return $this->database->get_var("SELECT id FROM $vpIdTableName WHERE `table` = \"{$this->dbSchema->getTableName($this->entityName)}\" AND vp_id = UNHEX('$vpid')");
    }

    private function buildUpdateQuery($updateData) {
        $id = $updateData['vp_id'];
        $tableName = $this->getPrefixedTableName($this->entityName);
        $query = "UPDATE {$tableName} JOIN (SELECT * FROM {$this->database->prefix}vp_id WHERE `table` = '{$this->dbSchema->getTableName($this->entityName)}') filtered_vp_id ON {$tableName}.{$this->idColumnName} = filtered_vp_id.id SET";
        foreach ($updateData as $key => $value) {
            if ($key == $this->idColumnName) continue;
            if (Strings::startsWith($key, 'vp_')) continue;
            $query .= " `$key` = " . (is_numeric($value) ? $value : '"' . mysql_real_escape_string($value) . '"') . ',';
        }
        $query[strlen($query) - 1] = ' '; // strip the last comma
        $query .= " WHERE filtered_vp_id.vp_id = UNHEX('$id')";
        return $query;
    }

    protected function buildCreateQuery($entity) {
        unset($entity[$this->idColumnName]);
        $columns = array_keys($entity);
        $columns = array_filter($columns, function ($column) {
            return !Strings::startsWith($column, 'vp_');
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

    private function createIdentifierRecord($vp_id, $id) {
        $query = "INSERT INTO {$this->getPrefixedTableName('vp_id')} (`table`, vp_id, id)
            VALUES (\"{$this->dbSchema->getTableName($this->entityName)}\", UNHEX('$vp_id'), $id)";
        $this->executeQuery($query);
    }

    private function deleteEntitiesWhichAreNotInStorage($entities) {
        if (count($entities) == 0)
            return;
        $vpIds = array_map(function ($entity) {
            return 'UNHEX("' . $entity['vp_id'] . '")';
        }, $entities);

        $ids = $this->database->get_col("SELECT id FROM {$this->getPrefixedTableName('vp_id')} " .
            "WHERE `table` = \"{$this->dbSchema->getTableName($this->entityName)}\" AND vp_id NOT IN (" . join(",", $vpIds) . ")");

        if (count($ids) == 0)
            return;

        $idsString = join(',', $ids);

        $this->executeQuery("DELETE FROM {$this->getPrefixedTableName($this->entityName)} WHERE {$this->idColumnName} IN ({$idsString})");
        $this->executeQuery("DELETE FROM {$this->getPrefixedTableName('vp_id')} WHERE `table` = \"{$this->dbSchema->getTableName($this->entityName)}\" AND id IN ({$idsString})"); // using cascade delete in mysql
    }



    //--------------------------------------
    // Step 2 - Fixing references
    //--------------------------------------

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
                    return join(ArrayUtils::parametrize($a), ' AND ');
                }, $usedReferences), ') OR (') . '))';
            $deleteQuery = "DELETE FROM {$referenceTableName} WHERE {$constraintOfAllExistingReferences} AND `table` = \"{$this->dbSchema->getTableName($this->entityName)}\"";
            $this->executeQuery($deleteQuery);
        }

        $references = $this->dbSchema->getEntityInfo($this->entityName)->references;
        foreach ($references as $reference => $_) { // update foreign keys by VersionPress references
            $updateQuery = "UPDATE {$this->getPrefixedTableName($this->entityName)} entity SET `{$reference}` =
            IFNULL((SELECT reference_id FROM {$this->getPrefixedTableName('vp_reference_details')} ref
            WHERE ref.id=entity.{$this->idColumnName} AND `table` = \"{$this->dbSchema->getTableName($this->entityName)}\" and reference = \"{$reference}\"), entity.{$reference})";
            $this->executeQuery($updateQuery);
        }
    }

    private function fixReferencesOfOneEntity($entity) {
        $references = $this->getAllReferences($entity);
        $tableName = $this->dbSchema->getTableName($this->entityName);

        $referencesDetails = array();
        foreach ($references as $referenceName => $reference) {
            if ($reference == "")
                continue;
            $referencesDetails[] = array(
                '`table`' => "\"" . $tableName . "\"",
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
            if (Strings::startsWith($key, 'vp_')) {
                $key = Strings::substring($key, 3);
                if (isset($referencesInfo[$key]))
                    $references[$key] = $value;
            }
        }

        return $references;
    }


    //--------------------------------------
    // Step 4 - entity specific actions
    //--------------------------------------

    /**
     * Specific Synchronizers might do entity-specific actions, for example, VersionPress\Synchronizers\PostsSynchronizer
     * updates comment count in the database (something we don't track in the storage).
     */
    protected function doEntitySpecificActions() {
    }



    //--------------------------------------
    // Helper functions
    //--------------------------------------


    private function getPrefixedTableName($tableName) {
        return $this->dbSchema->getPrefixedTableName($tableName);
    }

    /**
     * Useful for debugging
     *
     * @param $query
     * @return false|int
     */
    private function executeQuery($query) {
        $result = $this->database->query($query);
        return $result;
    }


}
