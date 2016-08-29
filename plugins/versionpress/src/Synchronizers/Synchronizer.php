<?php
namespace VersionPress\Synchronizers;

use Nette\Utils\Strings;
use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\EntityInfo;
use VersionPress\Database\ShortcodesReplacer;
use VersionPress\Database\VpidRepository;
use VersionPress\Storages\Storage;
use VersionPress\Utils\AbsoluteUrlReplacer;
use VersionPress\Utils\QueryLanguageUtils;
use VersionPress\Utils\ReferenceUtils;
use VersionPress\Utils\WordPressCacheUtils;

/**
 * Synchronizer synchronizes entities from {@link Storage storages} back to the database.
 *
 * Synchronizer does work that is kind of opposite to the ones of storages but with one major
 * difference: while storages usually add or delete entities one by one or by small amounts,
 * synchronizer usually operates over all entities or some subset (see $entitiesToSynchronize).
 *
 * Synchronizers are run by the {@link VersionPress\Synchronizers\SynchronizationProcess}.
 */
class Synchronizer
{

    const SYNCHRONIZE_EVERYTHING = 'everything';
    const SYNCHRONIZE_MN_REFERENCES = 'mn-references';
    const DO_ENTITY_SPECIFIC_ACTIONS = 'entity-specific-actions';
    const COMPUTE_COLUMN_VALUES = 'compute-column-values';
    const REPLACE_SHORTCODES = 'replace-shortcodes';

    private $entityName;
    private $idColumnName;

    /** @var Storage */
    private $storage;

    /** @var Database */
    protected $database;

    /** @var DbSchemaInfo */
    private $dbSchema;

    /** @var EntityInfo */
    private $entityInfo;

    /** @var string */
    private $tableName;

    /** @var string */
    private $prefixedTableName;

    /** @var AbsoluteUrlReplacer */
    private $urlReplacer;

    /** @var ShortcodesReplacer */
    private $shortcodesReplacer;

    /** @var array|null */
    protected $entities = null;

    protected $passNumber = 0;

    /** @var bool */
    private $selectiveSynchronization;
    private $entitiesToSynchronize;
    private $deletedIds;
    /**
     * @var VpidRepository
     */
    private $vpidRepository;

    /**
     * @param Storage $storage Specific Synchronizers will use specific storage types, see SynchronizerFactory
     * @param Database $database
     * @param EntityInfo $entityInfo
     * @param DbSchemaInfo $dbSchemaInfo
     * @param VpidRepository $vpidRepository
     * @param AbsoluteUrlReplacer $urlReplacer
     * @param ShortcodesReplacer $shortcodesReplacer
     */
    public function __construct(
        Storage $storage,
        Database $database,
        EntityInfo $entityInfo,
        DbSchemaInfo $dbSchemaInfo,
        VpidRepository $vpidRepository,
        AbsoluteUrlReplacer $urlReplacer,
        ShortcodesReplacer $shortcodesReplacer
    ) {
        $this->storage = $storage;
        $this->database = $database;
        $this->entityInfo = $entityInfo;
        $this->dbSchema = $dbSchemaInfo;
        $this->vpidRepository = $vpidRepository;
        $this->urlReplacer = $urlReplacer;
        $this->entityName = $entityInfo->entityName;
        $this->shortcodesReplacer = $shortcodesReplacer;
        $this->idColumnName = $this->entityInfo->idColumnName;
        $this->tableName = $this->entityInfo->tableName;
        $this->prefixedTableName = $this->database->prefix . $this->tableName;
    }

    /**
     * Synchronizes entities from storage to the database. It generally only works with tracked
     * entities, i.e. the ignored (untracked) rows in the database are left untouched. The rows
     * corresponding to tracked entities are usually in sync with the storage after this method
     * is done. It may happen that the synchronizer cannot synchronize everything in the first
     * pass. Because of this, the synchronize method takes a task for sychronization (usually
     * "everything" for the first pass) and returns a list of tasks that aren't done yet. It's
     * up to the SynchronizationProcess to call the synchronize method again with this tasks
     * when the previous pass is done.
     *
     * If the $entitiesToSynchronize is null, the synchronizer will synchronize all entities.
     * If it's an array, the synchronizer will synchronize only those entities.
     *
     * @param string $task
     * @param array $entitiesToSynchronize List of VPIDs and their possible parents
     *                                     {@see SynchronizationProcess::synchronize()}
     * @return string[]
     */
    public function synchronize($task, $entitiesToSynchronize = null)
    {
        $entityName = $this->entityName;

        $this->passNumber += 1;
        $this->selectiveSynchronization = is_array($entitiesToSynchronize);
        $this->entitiesToSynchronize = $entitiesToSynchronize;

        $this->maybeInit($entitiesToSynchronize);
        $entities = $this->entities;
        $remainingTasks = [];

        do_action("vp_before_synchronization_{$entityName}");

        if ($task === Synchronizer::SYNCHRONIZE_EVERYTHING) {
            $this->updateDatabase($entities);

            $fixedMnReferences = $this->fixMnReferences($entities);
            $doneEntitySpecificActions = $this->doEntitySpecificActions();

            if (!$doneEntitySpecificActions) {
                $remainingTasks[] = self::DO_ENTITY_SPECIFIC_ACTIONS;
            }

            if (!$fixedMnReferences) {
                $remainingTasks[] = self::SYNCHRONIZE_MN_REFERENCES;
            }

            if ($this->shortcodesReplacer->entityCanContainShortcodes($entityName)) {
                $remainingTasks[] = self::REPLACE_SHORTCODES;
            }

            $remainingTasks[] = self::COMPUTE_COLUMN_VALUES;
        }

        if ($task === self::SYNCHRONIZE_MN_REFERENCES) {
            $this->fixMnReferences($entities);
        }

        if ($task === self::COMPUTE_COLUMN_VALUES) {
            $this->computeColumnValues();
        }

        if ($task === self::DO_ENTITY_SPECIFIC_ACTIONS) {
            $this->doEntitySpecificActions();
        }

        if ($task === self::REPLACE_SHORTCODES) {
            $this->restoreShortcodesInAllEntities();
        }

        do_action("vp_after_synchronization_{$entityName}");

        return $remainingTasks;
    }

    private function maybeInit($entitiesToSynchronize)
    {
        if ($this->entities !== null) {
            return;
        }

        $entities = $this->loadEntitiesFromStorage($entitiesToSynchronize);
        $entities = array_map(function ($entity) {
            return $this->vpidRepository->restoreForeignKeys($this->entityName, $entity);
        }, $entities);

        $this->entities = $entities;
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
    private function loadEntitiesFromStorage($entitiesToSynchronize)
    {
        if ($this->selectiveSynchronization) {
            $entities = [];
            foreach ($entitiesToSynchronize as $entityToSynchronize) {
                if ($this->storage->exists($entityToSynchronize['vp_id'], $entityToSynchronize['parent'])) {
                    $entities[] = $this->storage->loadEntity(
                        $entityToSynchronize['vp_id'],
                        $entityToSynchronize['parent']
                    );
                }
            }
        } else {
            $entities = $this->storage->loadAll();
        }
        $entities = $this->maybeStripMetaEntities($entities);
        return $entities;
    }

    /**
     * Strips meta entities from their parents. Called after entities have been loaded from storage.
     *
     * @param array $entities Entities as loaded from the storage
     * @return array Entities without meta entities
     */
    private function maybeStripMetaEntities($entities)
    {
        foreach ($entities as &$entity) {
            foreach ($entity as $field => $value) {
                if (Strings::match($field, "/.*#[0-9a-f]+/i")) {
                    unset($entity[$field]);
                }
            }
        }

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
    private function updateDatabase($entities)
    {
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
    protected function filterEntities($entities)
    {
        return array_map(function ($entity) {
            return $this->urlReplacer->restore($entity);
        }, $entities);
    }

    private function addOrUpdateEntities($entities)
    {
        foreach ($entities as $entity) {
            $vpId = $entity[$this->entityInfo->vpidColumnName];

            if ($this->existsInDatabase($vpId)) {
                $this->updateEntityInDatabase($entity);
            } else {
                $this->createEntityInDatabase($entity);
            }
        }
    }

    private function updateEntityInDatabase($entity)
    {
        $updateQuery = $this->buildUpdateQuery($entity);
        $this->executeQuery($updateQuery);
    }

    private function createEntityInDatabase($entity)
    {
        $createQuery = $this->buildCreateQuery($entity);
        $this->executeQuery($createQuery);

        if ($this->entityInfo->usesGeneratedVpids) {
            $id = $this->database->insert_id;
            $this->createIdentifierRecord($entity['vp_id'], $id);
        }
    }

    /**
     * True if `vp_id` record is found in the database
     *
     * @param string $vpid
     * @return bool
     */
    private function existsInDatabase($vpid)
    {
        if ($this->entityInfo->hasNaturalVpid) {
            $vpidColumnName = $this->entityInfo->vpidColumnName;
            $query = $this->database->prepare("SELECT `$vpidColumnName` FROM `{$this->prefixedTableName}` WHERE `{$vpidColumnName}` = %s", $vpid);
        } else {
            $query = "SELECT id FROM {$this->database->vp_id} WHERE `table` = \"{$this->tableName}\" AND vp_id = UNHEX('$vpid')";
        }

        return (bool)$this->database->get_var($query);
    }

    private function buildUpdateQuery($updateData)
    {

        return $this->entityInfo->hasNaturalVpid
            ? $this->buildUpdateQueryForEntityWithNaturalVpid($updateData)
            : $this->buildUpdateQueryForEntityWithGeneratedVpid($updateData);
    }

    private function buildUpdateQueryForEntityWithNaturalVpid($updateData)
    {
        $vpid = $updateData[$this->idColumnName];

        $query = "UPDATE {$this->prefixedTableName} SET";
        foreach ($updateData as $key => $value) {
            $query .= $this->database->prepare("`{$key}` = %s,", $value);
        }

        $query[strlen($query) - 1] = ' '; // strip the last comma

        $query .= $this->database->prepare(" WHERE `{$this->idColumnName}` = %s", $vpid);
        return $query;
    }

    private function buildUpdateQueryForEntityWithGeneratedVpid($updateData)
    {
        $id = $updateData['vp_id'];
        $query = "UPDATE {$this->prefixedTableName}
                  JOIN (SELECT * FROM {$this->database->vp_id} WHERE `table` = '{$this->tableName}') filtered_vp_id
                  ON {$this->prefixedTableName}.{$this->idColumnName} = filtered_vp_id.id SET";
        foreach ($updateData as $key => $value) {
            if ($key == $this->idColumnName) {
                continue;
            }
            if (Strings::startsWith($key, 'vp_')) {
                continue;
            }
            $query .= " `$key` = " . (is_numeric($value) ? $value : '"' . $this->database->_escape($value) . '"') . ',';
        }
        $query[strlen($query) - 1] = ' '; // strip the last comma
        $query .= " WHERE filtered_vp_id.vp_id = UNHEX('$id')";
        return $query;
    }

    protected function buildCreateQuery($entity)
    {
        if ($this->entityInfo->usesGeneratedVpids) {
            unset($entity[$this->idColumnName]);
        }

        $columns = array_keys($entity);
        $columns = array_filter($columns, function ($column) {
            return !Strings::startsWith($column, 'vp_');
        });
        $columnsString = join(', ', array_map(function ($column) {
            return "`$column`";
        }, $columns));

        $query = "INSERT INTO {$this->prefixedTableName} ({$columnsString}) VALUES (";

        foreach ($columns as $column) {
            $query .= (is_numeric($entity[$column])
                    ? $entity[$column]
                    : '"' . $this->database->_escape($entity[$column]) . '"') . ", ";
        }

        $query[strlen($query) - 2] = ' '; // strip the last comma
        $query .= ");";
        return $query;
    }

    private function createIdentifierRecord($vpid, $id)
    {
        $query = "INSERT INTO {$this->database->vp_id} (`table`, vp_id, id)
            VALUES (\"{$this->tableName}\", UNHEX('$vpid'), $id)";
        $this->executeQuery($query);
    }

    private function deleteEntitiesWhichAreNotInStorage($entities)
    {
        if ($this->selectiveSynchronization) {
            $savedVpIds = array_column($entities, $this->entityInfo->vpidColumnName);
            $vpIdsToSynchronize = array_column($this->entitiesToSynchronize, 'vp_id');

            if ($this->entityInfo->hasNaturalVpid) {
                $sql = "SELECT `{$this->idColumnName}` FROM {$this->prefixedTableName} ";
                $sql .= sprintf("WHERE {$this->idColumnName} IN (\"%s\") ", join('", "', $vpIdsToSynchronize));
                $sql .= sprintf("AND {$this->idColumnName} NOT IN (\"%s\")", join('", "', $savedVpIds));
            } else {
                $sql = sprintf('SELECT id FROM %s WHERE `table` = "%s" ', $this->database->vp_id, $this->tableName);
                $sql .= sprintf('AND HEX(vp_id) IN ("%s") ', join('", "', $vpIdsToSynchronize));
                $sql .= sprintf('AND HEX(vp_id) NOT IN ("%s")', join('", "', $savedVpIds));
            }

            $ids = $this->database->get_col($sql);
        } else {
            $allVpids = array_column($entities, $this->entityInfo->vpidColumnName);

            if ($this->entityInfo->hasNaturalVpid) {
                $rules = $this->entityInfo->getRulesForIgnoredEntities();
                $restrictionForIgnoredEntities = join(' OR ', array_map(function ($rule) {
                    $restrictionPart = QueryLanguageUtils::createSqlRestrictionFromRule($rule);
                    return "($restrictionPart)";
                }, $rules));


                $sql = "SELECT `{$this->idColumnName}` FROM {$this->prefixedTableName} WHERE NOT ($restrictionForIgnoredEntities)";
                if (count($allVpids) > 0) {
                    $sql .= " AND `{$this->idColumnName}` NOT IN (\"" . join('", "', $allVpids) . "\")";
                }
            } else {
                $sql = "SELECT id FROM {$this->database->vp_id} WHERE `table` = \"{$this->tableName}\"" .
                    (count($allVpids) > 0 ? "AND HEX(vp_id) NOT IN (\"" . join('", "', $allVpids) . "\")" : "");
            }

            $ids = $this->database->get_col($sql);
        }

        $this->deletedIds = $ids;

        if (count($ids) == 0) {
            return;
        }

        $idsString = join("', '", $ids);

        $this->executeQuery("DELETE FROM {$this->prefixedTableName} WHERE {$this->idColumnName} IN ('{$idsString}')");
        $this->executeQuery("DELETE FROM {$this->database->vp_id} WHERE `table` = \"{$this->tableName}\" AND id IN ('{$idsString}')");
    }



    //--------------------------------------
    // Step 3 - Fixing references
    //--------------------------------------

    private function fixMnReferences($entities)
    {
        $referencesToSave = $this->getExistingMnReferences($entities);
        $vpIdsToLoad = $this->getAllVpIdsUsedInReferences($referencesToSave);
        $idMap = $this->getIdsForVpIds($vpIdsToLoad);
        $hasAllIds = $this->idMapContainsAllVpIds($idMap, $vpIdsToLoad);

        if (!$hasAllIds) {
            return false;
        }

        foreach ($referencesToSave as $reference => $relations) {
            if ($this->entityInfo->isVirtualReference($reference)) {
                continue;
            }

            $referenceDetails = ReferenceUtils::getMnReferenceDetails($this->dbSchema, $this->entityName, $reference);
            $prefixedTable = $this->getPrefixedTableName($referenceDetails['junction-table']);
            $sourceColumn = $referenceDetails['source-column'];
            $targetColumn = $referenceDetails['target-column'];

            $valuesForInsert = array_map(function ($relation) use ($idMap) {
                $sourceId = $idMap[$relation['vp_id']];
                $targetId = $idMap[$relation['referenced_vp_id']];
                return "($sourceId, $targetId)";
            }, $relations);

            $sql = sprintf(
                "SELECT id FROM %s WHERE HEX(vp_id) IN ('%s')",
                $this->getPrefixedTableName('vp_id'),
                join("', '", array_map(function ($entity) {
                    return $entity['vp_id'];
                }, $entities))
            );
            $processedIds = array_merge($this->database->get_col($sql), $this->deletedIds);

            if ($this->selectiveSynchronization) {
                if (count($processedIds) > 0) {
                    $this->database->query(
                        "DELETE FROM $prefixedTable WHERE $sourceColumn IN (" . join(", ", $processedIds) . ")"
                    );
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

    private function getIdsForVpIds($referencesToUpdate)
    {
        if (count($referencesToUpdate) === 0) {
            return [[0, 0]];
        }

        $vpIdTable = $this->getPrefixedTableName('vp_id');
        $vpIds = array_map(function ($vpId) {
            return 'UNHEX("' . $vpId . '")';
        }, $referencesToUpdate);

        $vpIdsRestriction = join(', ', $vpIds);

        $result = $this->database->get_results(
            "SELECT HEX(vp_id), id FROM $vpIdTable WHERE vp_id IN ($vpIdsRestriction)",
            ARRAY_N
        );
        $result[] = [0, 0];
        return array_combine(array_column($result, 0), array_column($result, 1));
    }

    //--------------------------------------
    // Step 4 - entity specific actions
    //--------------------------------------

    /**
     * Specific Synchronizers might do entity-specific actions, for example, clean cache.
     *
     * @return bool If false, the method will be called again in a second pass.
     */
    protected function doEntitySpecificActions()
    {
        $cleanCache = $this->entityInfo->cleanCache;
        foreach ($cleanCache as $cacheType => $idColumn) {

            if (is_array($idColumn)) {
                $cacheType = key($idColumn);
                $idColumn = $idColumn[$cacheType];
            }

            if ($idColumn === 'id' || $idColumn === 'vpid') {
                $ids = array_column($this->entities, $this->entityInfo->vpidColumnName);
            } else {
                $ids = array_column($this->entities, "vp_{$idColumn}");
            }

            WordPressCacheUtils::cleanCache($cacheType, $ids, $this->database);
        }
        return true;
    }

    /**
     * Specific Entities might contain ignored colums, which values should be computed on synchronizing process e.g. posts.
     */
    protected function computeColumnValues()
    {
        foreach ($this->entityInfo->getIgnoredColumns() as $columnName => $function) {
            call_user_func($function, $this->database);
        }
    }

    //--------------------------------------
    // Helper functions
    //--------------------------------------


    private function getPrefixedTableName($tableName)
    {
        return $this->database->prefix . $tableName;
    }

    /**
     * Useful for debugging
     *
     * @param $query
     * @return false|int
     */
    private function executeQuery($query)
    {
        $result = $this->database->query($query);
        return $result;
    }

    /**
     * @param $entities
     * @return array
     */
    private function getExistingMnReferences($entities)
    {
        $mnReferences = $this->entityInfo->mnReferences;

        $referencesToFix = [];
        foreach ($entities as $entity) {
            foreach ($mnReferences as $reference => $referencedEntity) {
                $vpReference = "vp_$referencedEntity";
                if (!isset($entity[$vpReference]) || count($entity[$vpReference]) == 0) {
                    continue;
                }

                foreach ($entity[$vpReference] as $referencedVpId) {
                    $referencesToFix[$reference][] = [
                        'vp_id' => $entity['vp_id'],
                        'referenced_vp_id' => $referencedVpId
                    ];
                }
            }
        }
        return $referencesToFix;
    }

    /**
     * @param $referencesToSave
     * @return array
     */
    private function getAllVpIdsUsedInReferences($referencesToSave)
    {
        $vpIds = [];
        foreach ($referencesToSave as $relations) {
            foreach ($relations as $relation) {
                $vpIds[] = $relation['vp_id'];
                $vpIds[] = $relation['referenced_vp_id'];
            }
        }

        return $vpIds;
    }

    private function idMapContainsAllVpIds($idMap, $vpIds)
    {
        foreach ($vpIds as $vpId) {
            if (!isset($idMap[$vpId])) {
                return false;
            }
        }
        return true;
    }

    private function restoreShortcodesInAllEntities()
    {
        foreach ($this->entities as $entity) {
            $replacedEntity = $this->shortcodesReplacer->restoreShortcodesInEntity($this->entityName, $entity);
            if ($entity != $replacedEntity) {
                $this->updateEntityInDatabase($replacedEntity);
            }
        }
    }
}
