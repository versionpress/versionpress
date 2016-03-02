<?php
namespace VersionPress\Database;

use VersionPress\Storages\Mirror;

/**
 * Bridge between hooks in {@see wpdb} and {@see Mirror}. Transforms WordPress data to the form suitable for Mirror.
 * Especially, it transforms WP ids to VPIDs.
 */
class WpdbMirrorBridge {

    /**
     * @var Mirror
     */
    private $mirror;

    /**
     * @var DbSchemaInfo
     */
    private $dbSchemaInfo;

    /**
     * @var \wpdb
     */
    private $database;
    /**
     * @var VpidRepository
     */
    private $vpidRepository;

    /** @var bool */
    private $disabled;

    function __construct($wpdb, Mirror $mirror, DbSchemaInfo $dbSchemaInfo, VpidRepository $vpidRepository) {
        $this->database = $wpdb;
        $this->mirror = $mirror;
        $this->dbSchemaInfo = $dbSchemaInfo;
        $this->vpidRepository = $vpidRepository;
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
        $data = $this->vpidRepository->replaceForeignKeysWithReferences($entityName, $data);

        foreach ($ids as $id) {
            $this->updateEntity($data, $entityName, $id);
        }
    }

    function delete($table, $where) {
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
                $where = $this->fillParentId($entityName, $where, $id);
            }

            $this->vpidRepository->deleteId($entityName, $id);
            $this->mirror->delete($entityName, $where);
        }
    }

    /**
     * @param $parsedQuery ParsedQueryData
     */
    function query($parsedQuery) {
        if ($this->disabled) {
            return;
        }
        $table = $parsedQuery->table;
        $entityInfo = $this->dbSchemaInfo->getEntityInfoByPrefixedTableName($table);

        if (!$entityInfo)
            return;

        $usesSqlFunctions = $parsedQuery->usesSqlFunctions;


        if ($parsedQuery->queryType == ParsedQueryData::UPDATE_QUERY && !$usesSqlFunctions) {
            $this->processUpdateQueryWithoutSqlFunctions($parsedQuery, $entityInfo);
        }
        if ($parsedQuery->queryType == ParsedQueryData::DELETE_QUERY && !$usesSqlFunctions) {
            $this->processDeleteQueryWithoutSqlFunctions($parsedQuery, $entityInfo);
        }
        if ($parsedQuery->queryType == ParsedQueryData::INSERT_QUERY && !$usesSqlFunctions) {
            $this->processInsertQueryWithoutSqlFunctions($parsedQuery, $entityInfo);
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

        $data['vp_id'] = $vpId;

        if ($this->dbSchemaInfo->isChildEntity($entityName)) {
            $entityInfo = $this->dbSchemaInfo->getEntityInfo($entityName);
            $parentVpReference = "vp_" . $entityInfo->parentReference;
            if (!isset($data[$parentVpReference])) {
                $table = $this->dbSchemaInfo->getPrefixedTableName($entityName);
                $parentTable = $this->dbSchemaInfo->getTableName($entityInfo->references[$entityInfo->parentReference]);
                $vpidTable = $this->dbSchemaInfo->getPrefixedTableName('vp_id');
                $parentVpidSql = "SELECT HEX(vpid.vp_id) FROM {$table} t JOIN {$vpidTable} vpid ON t.{$entityInfo->parentReference} = vpid.id AND `table` = '{$parentTable}' WHERE {$entityInfo->idColumnName} = $id";
                $parentVpid = $this->database->get_var($parentVpidSql);
                $data[$parentVpReference] = $parentVpid;
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

    private function fillParentId($metaEntityName, $where, $id) {
        $entityInfo = $this->dbSchemaInfo->getEntityInfo($metaEntityName);
        $parentReference = $entityInfo->parentReference;

        $parent = $entityInfo->references[$parentReference];
        $vpIdTable = $this->dbSchemaInfo->getPrefixedTableName('vp_id');
        $entityTable = $this->dbSchemaInfo->getPrefixedTableName($metaEntityName);
        $parentTable = $this->dbSchemaInfo->getTableName($parent);
        $idColumnName = $this->dbSchemaInfo->getEntityInfo($metaEntityName)->idColumnName;

        $where["vp_{$parentReference}"] = $this->database->get_var("SELECT HEX(vp_id) FROM $vpIdTable WHERE `table` = '{$parentTable}' AND ID = (SELECT {$parentReference} FROM $entityTable WHERE {$idColumnName} = $id)");
        return $where;
    }

    /**
     * Disables all actions. Useful for deactivating VersionPress.
     */
    public function disable() {
        $this->disabled = true;
    }

    /**
     * @param $parsedQuery ParsedQueryData
     */
    private function processUpdateQueryWithoutSqlFunctions($parsedQuery, $entityInfo) {


        foreach ($parsedQuery->ids as $id) {
            $stringifiedId = "'" . $id . "'";
            $data = $this->database->get_results("SELECT * FROM {$parsedQuery->table} WHERE {$parsedQuery->idColumn} = {$stringifiedId}", ARRAY_A)[0];
            $data = $this->vpidRepository->replaceForeignKeysWithReferences($entityInfo->entityName, $data);
            $this->updateEntity($data, $entityInfo->entityName, $stringifiedId);
        }
    }
    private function processDeleteQueryWithoutSqlFunctions($parsedQuery, $entityInfo) {
        if (!$entityInfo->usesGeneratedVpids) {
            foreach ($parsedQuery->ids as $id) {
                $stringifiedId = "'" . $id . "'";
                $where[$parsedQuery->idColumn] = $stringifiedId;
                $this->vpidRepository->deleteId($entityInfo->entityName, $stringifiedId);
                $this->mirror->delete($entityInfo->entityName, $where);
            }
            return;
        }
        foreach ($parsedQuery->ids as $id) {
            $stringifiedId = "'" . $id . "'";
            $where['vp_id'] = $this->vpidRepository->getVpidForEntity($entityInfo->entityName, $id);
            if (!$where['vp_id']) {
                continue; // already deleted - deleting postmeta is sometimes called twice
            }

            if ($this->dbSchemaInfo->isChildEntity($entityInfo->entityName) && !isset($where["vp_{$entityInfo->parentReference}"])) {
                $where = $this->fillParentId($entityInfo->entityName, $where, $id);
            }

            $this->vpidRepository->deleteId($entityInfo->entityName, $stringifiedId);
            $this->mirror->delete($entityInfo->entityName, $where);
        }
    }

    private function processInsertQueryWithoutSqlFunctions($parsedQuery, $entityInfo) {
        $data = $this->vpidRepository->replaceForeignKeysWithReferences($entityInfo->entityName, $parsedQuery->data);
        $shouldBeSaved = $this->mirror->shouldBeSaved($entityInfo->entityName, $data);

        if (!$shouldBeSaved) {
            return;
        }
        
        $id = $this->database->insert_id;
        $entitiesCount = count($parsedQuery->data);

        for($i=0;$i<$entitiesCount;$i++) {
            $data = $this->vpidRepository->identifyEntity($entityInfo->entityName, $data[$i], ($id-$i));
            $this->mirror->save($entityInfo->entityName, $data);
        }
    }

}


