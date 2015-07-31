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
        $data = array_merge($data, $where);

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

        if (!$entityInfo) return;

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

            if (($entityName === 'postmeta' && !isset($where['vp_post_id'])) ||
                ($entityName === 'usermeta' && !isset($where['vp_user_id']))) {
                $where = $this->fillParentId($entityName, $where, $id);
            }

            $this->vpidRepository->deleteId($entityName, $id);
            $this->mirror->delete($entityName, $where);
        }
    }

    private function getUsermetaId($user_id, $meta_key) {
        $getMetaIdSql = "SELECT umeta_id FROM {$this->database->prefix}usermeta WHERE meta_key = \"$meta_key\" AND user_id = $user_id";
        return $this->database->get_var($getMetaIdSql);
    }

    private function getPostMetaId($post_id, $meta_key) {
        $getMetaIdSql = "SELECT meta_id FROM {$this->database->prefix}postmeta WHERE meta_key = \"$meta_key\" AND post_id = $post_id";
        return $this->database->get_var($getMetaIdSql);
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

            if (!$this->mirror->shouldBeSaved('postmeta', $meta)) {
                continue;
            }

            $meta = $this->vpidRepository->identifyEntity('postmeta', $meta, $meta['meta_id']);
            $this->mirror->save('postmeta', $meta);
        }
    }

    private function detectAllAffectedIds($entityName, $data, $where) {
        $idColumnName = $this->dbSchemaInfo->getEntityInfo($entityName)->idColumnName;
        $ids = array();

        if ($entityName === 'usermeta') {
            return array($this->getUsermetaId($data['user_id'], $data['meta_key']));
        } elseif ($entityName === 'postmeta') {
            if (isset($data['meta_id'])) {
                return array($ids[] = $data['meta_id']);
            }
            return array($this->getPostMetaId($data['post_id'], $data['meta_key']));
        } elseif (isset($where[$idColumnName])) {
            $ids[] = $where[$idColumnName];
            return $ids;
        } else {
            $ids = $this->getIdsForRestriction($entityName, $where);
            return $ids;
        }
    }

    private function fillParentId($metaEntityName, $where, $id) {
        $parent = $metaEntityName === 'postmeta' ? 'post' : 'user';
        $vpIdTable = $this->dbSchemaInfo->getPrefixedTableName('vp_id');
        $postMetaTable = $this->dbSchemaInfo->getPrefixedTableName($metaEntityName);
        $parentTable = $this->dbSchemaInfo->getTableName($parent);
        $idColumnName = $this->dbSchemaInfo->getEntityInfo($metaEntityName)->idColumnName;

        $where["vp_{$parent}_id"] = $this->database->get_var("SELECT HEX(vp_id) FROM $vpIdTable WHERE `table` = '{$parentTable}' AND ID = (SELECT {$parent}_id FROM $postMetaTable WHERE {$idColumnName} = $id)");
        return $where;
    }

    /**
     * Disables all actions. Useful for deactivating VersionPress.
     */
    public function disable() {
        $this->disabled = true;
    }

}
