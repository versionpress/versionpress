<?php
namespace VersionPress\Synchronizers;

use Nette\Utils\Strings;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Storages\MetaEntityStorage;
use VersionPress\Storages\Storage;
use VersionPress\Utils\ArrayUtils;
use VersionPress\Utils\ReferenceUtils;
use wpdb;

/**
 * Base class for synchronizers that work with generated VPIDs.
 *
 * TODO this needs a better name
 */
abstract class SynchronizerBase implements Synchronizer {

    const SYNCHRONIZE_MN_REFERENCES = 'mn-references';
    const DO_ENTITY_SPECIFIC_ACTIONS = 'entity-specific-actions';

    private $entityName;
    private $idColumnName;

    /** @var Storage */
    private $storage;

    /** @var wpdb */
    private $database;

    /** @var DbSchemaInfo */
    private $dbSchema;

    /** @var array|null */
    private $entities = null;

    protected $passNumber = 0;

    /** @var bool */
    private $selectiveSynchronization;
    private $entitiesToSynchronize;
    private $deletedIds;

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

    function synchronize($task, $entitiesToSynchronize = null) {
        $this->passNumber += 1;
        $this->selectiveSynchronization = is_array($entitiesToSynchronize);
        $this->entitiesToSynchronize = $entitiesToSynchronize;

        $this->maybeInit($entitiesToSynchronize);
        $entities = $this->entities;
        $remainingTasks = array();

        if ($task === Synchronizer::SYNCHRONIZE_EVERYTHING) {
            $this->updateDatabase($entities);
            $this->fixSimpleReferences($entities);
            $fixedMnReferences = $this->fixMnReferences($entities);
            $doneEntitySpecificActions = $this->doEntitySpecificActions();

            if (!$doneEntitySpecificActions) {
                $remainingTasks[] = self::DO_ENTITY_SPECIFIC_ACTIONS;
            }

            if (!$fixedMnReferences) {
                $remainingTasks[] = self::SYNCHRONIZE_MN_REFERENCES;
            }
        }

        if ($task === self::SYNCHRONIZE_MN_REFERENCES) {
            $this->fixMnReferences($entities);
        }

        if ($task === self::DO_ENTITY_SPECIFIC_ACTIONS) {
            $this->doEntitySpecificActions();
        }

        return $remainingTasks;
    }

    private function maybeInit($entitiesToSynchronize) {
        if ($this->entities !== null) {
            return;
        }

        $this->entities = $this->loadEntitiesFromStorage($entitiesToSynchronize);
    }

    //--------------------------------------
    // Step 1 - loading entities from storage
    //--------------------------------------

    /**
     * Loads entities from storage and gives subclasses the chance to transform them, see
     * {@see transformEntities()}.
     *
     * @param $entitiesToSynchronize
     * @return array
     */
    private function loadEntitiesFromStorage($entitiesToSynchronize) {
        if ($this->selectiveSynchronization) {
            $entities = array();
            if (!($this->storage instanceof MetaEntityStorage)) {
                $entitiesToSynchronize = array_map(function ($entity) { $entity['parent'] = null; return $entity; }, $entitiesToSynchronize);
                $entitiesToSynchronize = array_unique($entitiesToSynchronize, SORT_REGULAR);
            }

            foreach ($entitiesToSynchronize as $entityToSynchronize) {
                if ($this->storage->exists($entityToSynchronize['vp_id'], $entityToSynchronize['parent'])) {
                    $entities[] = $this->storage->loadEntity($entityToSynchronize['vp_id'], $entityToSynchronize['parent']);
                }
            }

        } else {
            $entities = $this->storage->loadAll();
        }
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
            $query .= " `$key` = " . (is_numeric($value) ? $value : '"' . $this->database->_escape($value) . '"') . ',';
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
            $query .= (is_numeric($entity[$column]) ? $entity[$column] : '"' . $this->database->_escape($entity[$column]) . '"') . ", ";
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
        if ($this->selectiveSynchronization) {
            $savedVpIds = array_map(function ($entity) { return $entity['vp_id']; }, $entities);
            $vpIdsToSynchronize = array_map(function ($entity) { return $entity['vp_id']; }, $this->entitiesToSynchronize);

            $sql = sprintf('SELECT id FROM %s WHERE `table` = "%s" ', $this->getPrefixedTableName('vp_id'), $this->dbSchema->getTableName($this->entityName));
            $sql .= sprintf('AND HEX(vp_id) IN ("%s") ', join('", "', $vpIdsToSynchronize));
            $sql .= sprintf('AND HEX(vp_id) NOT IN ("%s")', join('", "', $savedVpIds));

            $ids = $this->database->get_col($sql);

        } else {
            $vpIdsUnhexed = array_map(function ($entity) {
                return 'UNHEX("' . $entity['vp_id'] . '")';
            }, $entities);

            $ids = $this->database->get_col("SELECT id FROM {$this->getPrefixedTableName('vp_id')} " .
                "WHERE `table` = \"{$this->dbSchema->getTableName($this->entityName)}\"" . (count($vpIdsUnhexed) > 0 ? "AND vp_id NOT IN (" . join(",", $vpIdsUnhexed) . ")" : ""));
        }

        $this->deletedIds = $ids;

        if (count($ids) == 0)
            return;

        $idsString = join(',', $ids);

        $this->executeQuery("DELETE FROM {$this->getPrefixedTableName($this->entityName)} WHERE {$this->idColumnName} IN ({$idsString})");
        $this->executeQuery("DELETE FROM {$this->getPrefixedTableName('vp_id')} WHERE `table` = \"{$this->dbSchema->getTableName($this->entityName)}\" AND id IN ({$idsString})");
    }



    //--------------------------------------
    // Step 3 - Fixing references
    //--------------------------------------

    private function fixSimpleReferences($entities) {
        if (!$this->dbSchema->getEntityInfo($this->entityName)->hasReferences) {
            return;
        }

        foreach ($entities as $entity) {
            $this->fixSimpleReferencesOfOneEntity($entity);
            $this->fixValueReferencesOfOneEntity($entity);
        }
    }

    /**
     * Fixes 1:N references
     *
     * @param $entity
     */
    private function fixSimpleReferencesOfOneEntity($entity) {
        $entityInfo = $this->dbSchema->getEntityInfo($this->entityName);
        $references = $entityInfo->references;

        $referencesToUpdate = array();
        foreach ($references as $reference => $referencedEntity) {
            $vpReference = "vp_$reference";
            if (isset($entity[$vpReference])) {
                $referencesToUpdate[$reference] = $entity[$vpReference];
            }
        }

        if (count($referencesToUpdate) === 0) {
            return;
        }

        $idMap = $this->getIdsForVpIds($referencesToUpdate);

        $entityTable = $this->dbSchema->getPrefixedTableName($this->entityName);
        $vpIdTable = $this->dbSchema->getPrefixedTableName('vp_id');
        $idColumnName = $entityInfo->idColumnName;

        $updateSql = "UPDATE $entityTable SET ";

        $newReferences = array_map(function ($vpId) use ($idMap) {
            return $idMap[$vpId];
        }, $referencesToUpdate);
        $updateSql .= join(", ", ArrayUtils::parametrize($newReferences));

        $updateSql .= " WHERE $idColumnName=(SELECT id FROM $vpIdTable WHERE vp_id=UNHEX(\"$entity[vp_id]\"))";
        $this->database->query($updateSql);
    }

    /**
     * Fixes value references
     *
     * @param $entity
     */
    private function fixValueReferencesOfOneEntity($entity) {
        $entityInfo = $this->dbSchema->getEntityInfo($this->entityName);
        $references = $entityInfo->valueReferences;

        $referencesToUpdate = array();
        foreach ($references as $reference => $referencedEntity) {
            list($sourceColumn, $sourceValue, $valueColumn) = array_values(ReferenceUtils::getValueReferenceDetails($reference));

            if (isset($entity[$sourceColumn]) && $entity[$sourceColumn] == $sourceValue && isset($entity[$valueColumn])) {
                $referencesToUpdate[$valueColumn] = $entity[$valueColumn];
            }
        }

        if (count($referencesToUpdate) === 0) {
            return;
        }

        $idMap = $this->getIdsForVpIds($referencesToUpdate);

        $entityTable = $this->dbSchema->getPrefixedTableName($this->entityName);
        $vpIdTable = $this->dbSchema->getPrefixedTableName('vp_id');
        $idColumnName = $entityInfo->idColumnName;

        $updateSql = "UPDATE $entityTable SET ";

        $newReferences = array_map(function ($vpId) use ($idMap) {
            return $idMap[$vpId];
        }, $referencesToUpdate);
        $updateSql .= join(", ", ArrayUtils::parametrize($newReferences));

        $updateSql .= " WHERE $idColumnName=(SELECT id FROM $vpIdTable WHERE vp_id=UNHEX(\"$entity[vp_id]\"))";
        $this->database->query($updateSql);
    }

    private function fixMnReferences($entities) {
        $entityInfo = $this->dbSchema->getEntityInfo($this->entityName);
        $mnReferences = $entityInfo->mnReferences;

        $referencesToSave = $this->getExistingMnReferences($entities);
        $vpIdsToLoad = $this->getAllVpIdsUsedInReferences($referencesToSave);
        $idMap = $this->getIdsForVpIds($vpIdsToLoad);
        $hasAllIds = $this->idMapContainsAllVpIds($idMap, $vpIdsToLoad);

        if (!$hasAllIds) {
            return false;
        }

        foreach ($referencesToSave as $reference => $relations) {
            if ($entityInfo->isVirtualReference($reference)) {
                continue;
            }

            $referenceDetails = ReferenceUtils::getMnReferenceDetails($this->dbSchema, $this->entityName, $reference);
            $prefixedTable = $this->dbSchema->getPrefixedTableName($referenceDetails['junction-table']);
            $sourceColumn = $referenceDetails['source-column'];
            $targetColumn = $referenceDetails['target-column'];

            $valuesForInsert = array_map(function ($relation) use ($idMap) {
                $sourceId = $idMap[$relation['vp_id']];
                $targetId = $idMap[$relation['referenced_vp_id']];
                return "($sourceId, $targetId)";
            }, $relations);

            $sql = sprintf("SELECT id FROM %s WHERE HEX(vp_id) IN ('%s')",
                $this->dbSchema->getPrefixedTableName('vp_id'),
                join("', '", array_map(function ($entity) { return $entity['vp_id']; }, $entities)));
            $processedIds = array_merge($this->database->get_col($sql), $this->deletedIds);

            if ($this->selectiveSynchronization) {
                if (count($processedIds) > 0) {
                    $this->database->query("DELETE FROM $prefixedTable WHERE $sourceColumn IN (" . join(", ", $processedIds) . ")");
                }
            } else {
                $this->database->query("TRUNCATE TABLE $prefixedTable");
            }

            $valuesString = join(", ", $valuesForInsert);
            $insertSql = "INSERT IGNORE INTO $prefixedTable ($sourceColumn, $targetColumn) VALUES $valuesString";
            $this->database->query($insertSql);
        }

        return true;
    }

    private function getIdsForVpIds($referencesToUpdate) {
        if (count($referencesToUpdate) === 0) {
            return array();
        }

        $vpIdTable = $this->dbSchema->getPrefixedTableName('vp_id');
        $vpIds = array_map(function ($vpId) {
            return 'UNHEX("' . $vpId . '")';
        }, $referencesToUpdate);

        $vpIdsRestriction = join(', ', $vpIds);

        $result = $this->database->get_results("SELECT HEX(vp_id), id FROM $vpIdTable WHERE vp_id IN ($vpIdsRestriction)", ARRAY_N);
        return array_combine(array_column($result, 0), array_column($result, 1));
    }

    //--------------------------------------
    // Step 4 - entity specific actions
    //--------------------------------------

    /**
     * Specific Synchronizers might do entity-specific actions, for example, VersionPress\Synchronizers\PostsSynchronizer
     * updates comment count in the database (something we don't track in the storage).
     *
     * @return bool If false, the method will be called again in a second pass.
     */
    protected function doEntitySpecificActions() {
        return true;
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

    /**
     * @param $entities
     * @return array
     */
    private function getExistingMnReferences($entities) {
        $entityInfo = $this->dbSchema->getEntityInfo($this->entityName);
        $mnReferences = $entityInfo->mnReferences;

        $referencesToFix = array();
        foreach ($entities as $entity) {
            foreach ($mnReferences as $reference => $referencedEntity) {
                $vpReference = "vp_$referencedEntity";
                if (!isset($entity[$vpReference]) || count($entity[$vpReference]) == 0) {
                    continue;
                }

                foreach ($entity[$vpReference] as $referencedVpId) {
                    $referencesToFix[$reference][] = array(
                        'vp_id' => $entity['vp_id'],
                        'referenced_vp_id' => $referencedVpId
                    );
                }
            }
        }
        return $referencesToFix;
    }

    /**
     * @param $referencesToSave
     * @return array
     */
    private function getAllVpIdsUsedInReferences($referencesToSave) {
        $vpIds = array();
        foreach ($referencesToSave as $relations) {
            foreach ($relations as $relation) {
                $vpIds[] = $relation['vp_id'];
                $vpIds[] = $relation['referenced_vp_id'];
            }
        }

        return $vpIds;
    }

    private function idMapContainsAllVpIds($idMap, $vpIds) {
        foreach ($vpIds as $vpId) {
            if (!isset($idMap[$vpId])) {
                return false;
            }
        }
        return true;
    }
}
