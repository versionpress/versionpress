<?php
namespace VersionPress\Database;

use VersionPress\Storages\Mirror;

/**
 * Bridge between hooks in {@see wpdb} and {@see Mirror}. Transforms WordPress data to the form suitable for Mirror.
 * Especially, it transforms WP ids to VPIDs.
 */
class WpdbMirrorBridge {

    /** @var Mirror */
    private $mirror;

    /** @var DbSchemaInfo */
    private $dbSchemaInfo;

    /** @var Database */
    private $database;

    /** @var VpidRepository */
    private $vpidRepository;

    /** @var ShortcodesReplacer */
    private $shortcodesReplacer;

    /** @var bool */
    private $disabled;

    function __construct($database, Mirror $mirror, DbSchemaInfo $dbSchemaInfo, VpidRepository $vpidRepository, ShortcodesReplacer $shortcodesReplacer) {
        $this->database = $database;
        $this->mirror = $mirror;
        $this->dbSchemaInfo = $dbSchemaInfo;
        $this->vpidRepository = $vpidRepository;
        $this->shortcodesReplacer = $shortcodesReplacer;
    }

    function insert($table, $data) {
        if ($this->disabled) {
            return;
        }

        $id = $this->database->insert_id;
        $entityInfo = $this->dbSchemaInfo->getEntityInfoByPrefixedTableName($table);

        if (!$entityInfo) {
            return;
        }

        $entityName = $entityInfo->entityName;
        $data = $this->vpidRepository->replaceForeignKeysWithReferences($entityName, $data);
        $shouldBeSaved = $this->mirror->shouldBeSaved($entityName, $data);

        if (!$shouldBeSaved) {
            return;
        }

        $data = $this->vpidRepository->identifyEntity($entityName, $data, $id);
        $data = $this->shortcodesReplacer->replaceShortcodesInEntity($entityName, $data);
        $this->mirror->save($entityName, $data);
    }

    function update($table, $data, $where) {
        if ($this->disabled) {
            return;
        }

        $entityInfo = $this->dbSchemaInfo->getEntityInfoByPrefixedTableName($table);

        if (!$entityInfo) {
            return;
        }

        $entityName = $entityInfo->entityName;
        $data = array_merge($where, $data);

        if (!$entityInfo->usesGeneratedVpids) { // options etc.
            $data = $this->vpidRepository->replaceForeignKeysWithReferences($entityName, $data);
            $this->mirror->save($entityName, $data);
            return;
        }

        $ids = $this->detectAllAffectedIds($entityName, $data, $where);

        foreach ($ids as $id) {
            $this->updateEntity($data, $entityName, $id);
        }
    }

    function delete($table, $where, $parentIds) {
        if ($this->disabled) {
            return;
        }

        $entityInfo = $this->dbSchemaInfo->getEntityInfoByPrefixedTableName($table);

        if (!$entityInfo)
            return;

        $entityName = $entityInfo->entityName;

        if (!$entityInfo->usesGeneratedVpids) {
            $this->mirror->delete($entityName, $where);
            return;
        }

        $ids = $this->detectAllAffectedIds($entityName, $where, $where);

        foreach ($ids as $id) {
            $where['vp_id'] = $this->vpidRepository->getVpidForEntity($entityName, $id);
            if (!$where['vp_id']) {
                continue; // already deleted - deleting postmeta is sometimes called twice
            }

            if ($this->dbSchemaInfo->isChildEntity($entityName) && !isset($where["vp_{$entityInfo->parentReference}"])) {
                $where["vp_{$entityInfo->parentReference}"] = $parentIds[$id];
            }

            $this->vpidRepository->deleteId($entityName, $id);
            $this->mirror->delete($entityName, $where);
        }
    }

    /**
     * Fill parentIds of child entities and returns them as associative array in `$id => $parentId` format.
     * Returns FALSE when table contains entity which is not an childEntity.
     *
     * @param $table
     * @param $where
     * @return array
     */
    public function getParentIdsBeforeDelete($table, $where) {
        $entityInfo = $this->dbSchemaInfo->getEntityInfoByPrefixedTableName($table);
        if (!$entityInfo) {
            return [];
        }
        if (!$this->dbSchemaInfo->isChildEntity($entityInfo->entityName)) {
            return [];
        }

        $ids = $this->detectAllAffectedIds($entityInfo->entityName, $where, $where);
        $parentIds = [];
        foreach ($ids as $id) {
            $parentIds[$id] = $this->fillParentId($entityInfo->entityName, $entityInfo, $id);
        }
        return $parentIds;

    }


    /**
     * @param ParsedQueryData $parsedQueryData
     */
    public function query($parsedQueryData) {
        if ($this->disabled) {
            return;
        }

        $entityInfo = $this->dbSchemaInfo->getEntityInfoByPrefixedTableName($parsedQueryData->table);

        if (!$entityInfo) {
            return;
        }

        switch ($parsedQueryData->queryType) {
            case ParsedQueryData::UPDATE_QUERY:
                $this->processUpdateQuery($parsedQueryData);
                break;
            case  ParsedQueryData::DELETE_QUERY:
                $this->processDeleteQuery($parsedQueryData, $entityInfo);
                break;
            case ParsedQueryData::INSERT_QUERY:
                $this->processInsertQuery($parsedQueryData);
                break;
            case ParsedQueryData::INSERT_UPDATE_QUERY:
                $this->processInsertUpdateQuery($parsedQueryData);
                break;
        }


    }

    /**
     * Returns all ids from DB suitable for given restriction.
     * E.g. all comment_id values where comment_post_id = 1
     * @param string $entityName
     * @param array $where
     * @return array
     */
    private function getIdsForRestriction($entityName, $where) {
        $idColumnName = $this->dbSchemaInfo->getEntityInfo($entityName)->idColumnName;
        $table = $this->dbSchemaInfo->getPrefixedTableName($entityName);

        $sql = "SELECT {$idColumnName} FROM {$table} WHERE ";
        $sql .= join(
            " AND ",
            array_map(
                function ($column) {
                    return "`$column` = %s";
                },
                array_keys($where)
            )
        );
        $ids = $this->database->get_col($this->database->prepare($sql, $where));
        return $ids;
    }

    private function updateEntity($data, $entityName, $id) {
        $vpId = $this->vpidRepository->getVpidForEntity($entityName, $id);

        $table = $this->dbSchemaInfo->getPrefixedTableName($entityName);
        $idColumnName = $this->dbSchemaInfo->getEntityInfo($entityName)->idColumnName;
        $data = $this->database->get_row("SELECT * FROM `$table` WHERE `$idColumnName` = '$id'", ARRAY_A);

        $data['vp_id'] = $vpId;
        $data = $this->vpidRepository->replaceForeignKeysWithReferences($entityName, $data);

        if ($this->dbSchemaInfo->isChildEntity($entityName)) {
            $entityInfo = $this->dbSchemaInfo->getEntityInfo($entityName);
            $parentVpReference = "vp_" . $entityInfo->parentReference;
            if (!isset($data[$parentVpReference])) {
                $data[$parentVpReference] = $this->fillParentId($entityName, $entityInfo, $id);
            }
        }

        $shouldBeSaved = $this->mirror->shouldBeSaved($entityName, $data);
        if (!$shouldBeSaved) {
            return;
        }

        $savePostmeta = !$vpId && $entityName === 'post'; // the post exists in DB for a while but until now it wasn't tracked, so we have to save its postmeta

        if (!$vpId) {
            $data = $this->vpidRepository->identifyEntity($entityName, $data, $id);
        }

        $data = $this->shortcodesReplacer->replaceShortcodesInEntity($entityName, $data);
        $this->mirror->save($entityName, $data);

        if (!$savePostmeta) {
            return;
        }

        $postmeta = $this->database->get_results("SELECT meta_id, meta_key, meta_value FROM {$this->database->postmeta} WHERE post_id = {$id}", ARRAY_A);
        foreach ($postmeta as $meta) {
            $meta['vp_post_id'] = $data['vp_id'];

            $meta = $this->vpidRepository->replaceForeignKeysWithReferences('postmeta', $meta);
            if (!$this->mirror->shouldBeSaved('postmeta', $meta)) {
                continue;
            }

            $meta = $this->vpidRepository->identifyEntity('postmeta', $meta, $meta['meta_id']);

            $meta = $this->shortcodesReplacer->replaceShortcodesInEntity('postmeta', $meta);
            $this->mirror->save('postmeta', $meta);
        }
    }

    /**
     * Returns all database IDs matching the restriction.
     * In most cases it returns ID from $where array.
     * For meta-entities it can find the ID by key and parent entity ID, if
     * the ID is missing in the $where array.
     * For all other cases see {@link WpdbMirrorBridge::getIdsForRestriction}.
     *
     * @param $entityName
     * @param $data
     * @param $where
     * @return array List of ids
     */
    private function detectAllAffectedIds($entityName, $data, $where) {
        $idColumnName = $this->dbSchemaInfo->getEntityInfo($entityName)->idColumnName;

        if (isset($where[$idColumnName])) {
            return array($where[$idColumnName]);
        }

        return $this->getIdsForRestriction($entityName, $where);
    }

    private function fillParentId($metaEntityName, $entityInfo, $id) {

        $parentReference = $entityInfo->parentReference;
        $parent = $entityInfo->references[$parentReference];
        $vpIdTable = $this->dbSchemaInfo->getPrefixedTableName('vp_id');
        $entityTable = $this->dbSchemaInfo->getPrefixedTableName($metaEntityName);
        $parentTable = $this->dbSchemaInfo->getTableName($parent);
        $idColumnName = $this->dbSchemaInfo->getEntityInfo($metaEntityName)->idColumnName;

        return $this->database->get_var("SELECT HEX(vp_id) FROM $vpIdTable WHERE `table` = '{$parentTable}' AND ID = (SELECT {$parentReference} FROM $entityTable WHERE {$idColumnName} = '$id')");

    }

    /**
     * Disables all actions. Useful for deactivating VersionPress.
     */
    public function disable() {
        $this->disabled = true;
    }

    /**
     * Processes ParsedQueryData from UPDATE query and stores updated entity/entities data into Storage.
     *
     * @param ParsedQueryData $parsedQueryData
     */
    private function processUpdateQuery($parsedQueryData) {

        foreach ($parsedQueryData->ids as $id) {
            $this->updateEntity([], $parsedQueryData->entityName, $id);
        }
    }

    /**
     * Process ParsedQueryData from DELETE query and deletes entity/entities data from Storage.
     * Source parsed query does not contain any special Sql functions (e.g. NOW)
     *
     * @param ParsedQueryData $parsedQueryData
     * @param $entityInfo
     */
    private function processDeleteQuery($parsedQueryData, $entityInfo) {
        if (!$entityInfo->usesGeneratedVpids) {
            foreach ($parsedQueryData->ids as $id) {
                $where[$parsedQueryData->idColumnName] = $id;
                $this->vpidRepository->deleteId($parsedQueryData->entityName, $id);
                $this->mirror->delete($parsedQueryData->entityName, $where);
            }
            return;
        }
        foreach ($parsedQueryData->ids as $id) {
            $where['vp_id'] = $this->vpidRepository->getVpidForEntity($parsedQueryData->entityName, $id);
            if (!$where['vp_id']) {
                continue; // already deleted - deleting postmeta is sometimes called twice
            }

            if ($this->dbSchemaInfo->isChildEntity($parsedQueryData->entityName)) {
                $parentVpReference = "vp_" . $entityInfo->parentReference;
                $where[$parentVpReference] = $this->fillParentId($parsedQueryData->entityName, $entityInfo, $id);
            }

            $this->vpidRepository->deleteId($parsedQueryData->entityName, $id);
            $this->mirror->delete($parsedQueryData->entityName, $where);
        }
    }

    /**
     * Process ParsedQueryData from INSERT query and stores affected entity into Storage.
     * Source parsed query does not contain any special Sql functions (e.g. NOW)
     *
     * @param ParsedQueryData $parsedQueryData
     */
    private function processInsertQuery($parsedQueryData) {


        $id = $this->database->insert_id;
        $entitiesCount = count($parsedQueryData->data);

        for ($i = 0; $i < $entitiesCount; $i++) {
            $data = $this->vpidRepository->replaceForeignKeysWithReferences($parsedQueryData->entityName, $parsedQueryData->data[$i]);
            $shouldBeSaved = $this->mirror->shouldBeSaved($parsedQueryData->entityName, $data);

            if (!$shouldBeSaved) {
                continue;
            }
            $data = $this->vpidRepository->identifyEntity($parsedQueryData->entityName, $data, ($id - $i));
            $this->mirror->save($parsedQueryData->entityName, $data);
        }
    }

    /**
     * Processes ParsedQueryData from INSERT ... ON DUPLICATE UPDATE query and stores changes into Storage
     *
     * @param ParsedQueryData $parsedQueryData
     */
    private function processInsertUpdateQuery($parsedQueryData) {

        if ($parsedQueryData->ids != 0) {
            $id = $parsedQueryData->ids;
            $data = $this->vpidRepository->replaceForeignKeysWithReferences($parsedQueryData->entityName, $parsedQueryData->data[0]);
            $shouldBeSaved = $this->mirror->shouldBeSaved($parsedQueryData->entityName, $data);

            if (!$shouldBeSaved) {
                return;
            }
            $data = $this->vpidRepository->identifyEntity($parsedQueryData->entityName, $data, $id);
            $this->mirror->save($parsedQueryData->entityName, $data);
        } else {
            $data = $this->database->get_results($parsedQueryData->sqlQuery, ARRAY_A)[0];
            $this->updateEntity($data, $parsedQueryData->entityName, $data[$parsedQueryData->idColumnName]);
        }

    }


}


