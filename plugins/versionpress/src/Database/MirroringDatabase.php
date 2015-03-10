<?php
namespace VersionPress\Database;

use Nette\Utils\Arrays;
use VersionPress\Storages\Mirror;
use VersionPress\Utils\ArrayUtils;
use VersionPress\Utils\IdUtil;

/**
 * Mirroring database sends every change in DB (insert, update, delete) to file mirror
 */
class MirroringDatabase extends ExtendedWpdb {

    /**
     * @var Mirror
     */
    private $mirror;

    /**
     * @var DbSchemaInfo
     */
    private $dbSchemaInfo;

    function __construct($dbUser, $dbPassword, $dbName, $dbHost, Mirror $mirror, DbSchemaInfo $dbSchemaInfo) {
        parent::__construct($dbUser, $dbPassword, $dbName, $dbHost);
        $this->mirror = $mirror;
        $this->dbSchemaInfo = $dbSchemaInfo;
    }

    function insert($table, $data, $format = null) {

        $result = parent::insert($table, $data, $format);

        if (defined('VP_DEACTIVATING')) {
            return $result;
        }

        $id = $this->insert_id;
        $tableName = $this->stripTablePrefix($table);
        $entityInfo = $this->dbSchemaInfo->getEntityInfoByTableName($tableName);

        if (!$entityInfo) return $result;

        $entityName = $entityInfo->entityName;
        $shouldBeSaved = $this->mirror->shouldBeSaved($entityName, $data);

        if (!$shouldBeSaved)
            return $result;

        if ($this->dbSchemaInfo->getEntityInfo($entityName)->usesGeneratedVpids) {
            $data['vp_id'] = $this->generateId();
            $this->saveId($entityName, $id, $data['vp_id']);
        }

        $data[$this->dbSchemaInfo->getEntityInfo($entityName)->idColumnName] = $id;

        $data = $this->fillId($entityName, $data, $id);
        $data = $this->replaceForeignKeysWithReferences($entityName, $data);
        $this->mirror->save($entityName, $data);

        $this->insert_id = $id; // it was reset by saving id and references
        return $result;
    }

    function update($table, $data, $where, $format = null, $where_format = null, $updateDatabase = true) {
        $result = $updateDatabase ? parent::update($table, $data, $where, $format, $where_format) : false;

        if (defined('VP_DEACTIVATING')) {
            return $result;
        }

        $entityInfo = $this->dbSchemaInfo->getEntityInfoByTableName($this->stripTablePrefix($table));

        if (!$entityInfo) return $result;

        $entityName = $entityInfo->entityName;

        $data = array_merge($data, $where);

        if ($this->dbSchemaInfo->getEntityInfo($entityName)->usesGeneratedVpids) {

            $idColumnName = $this->dbSchemaInfo->getEntityInfo($entityName)->idColumnName;
            $ids = array();

            if ($entityName === 'usermeta') {
                $ids[] = $this->getUsermetaId($data['user_id'], $data['meta_key']);
            } elseif ($entityName === 'postmeta') {
                $ids[] = $this->getPostMetaId($data['post_id'], $data['meta_key']);
            } elseif (isset($where[$idColumnName])) {
                $ids[] = $where[$idColumnName];
            } else {
                $ids = $this->getIdsForRestriction($entityName, $where);
            }

            foreach ($ids as $id) {
                $vpId = $this->getVpId($entityName, $id);

                $data['vp_id'] = $vpId;
                $shouldBeSaved = $this->mirror->shouldBeSaved($entityName, $data);
                if (!$shouldBeSaved) {
                    continue;
                }

                if (!$vpId) {
                    $data['vp_id'] = $this->generateId();
                    $this->saveId($entityName, $id, $data['vp_id']);
                }

                $data = $this->replaceForeignKeysWithReferences($entityName, $data);
                $this->mirror->save($entityName, $data);
            }
            return $result;
        }
        $data = $this->replaceForeignKeysWithReferences($entityName, $data);
        $this->mirror->save($entityName, $data);
        return $result;
    }

    function delete($table, $where, $where_format = null, $updateDatabase = true) {
        $result = $updateDatabase ? parent::delete($table, $where, $where_format) : false;

        if (defined('VP_DEACTIVATING')) {
            return $result;
        }

        $entityInfo = $this->dbSchemaInfo->getEntityInfoByTableName($this->stripTablePrefix(($table)));

        if (!$entityInfo) return $result;

        $entityName = $entityInfo->entityName;

        if ($this->dbSchemaInfo->getEntityInfo($entityName)->usesGeneratedVpids) {
            $ids = array();
            $idColumnName = $this->dbSchemaInfo->getEntityInfo($entityName)->idColumnName;
            if (isset($where[$idColumnName])) {
                $ids[] = $where[$idColumnName];
            } else {
                $ids = $this->getIdsForRestriction($entityName, $where);
            }

            foreach ($ids as $id) {
                $where['vp_id'] = $this->getVpId($entityName, $id);
                if (!$where['vp_id']) {
                    continue; // already deleted - deleting postmeta is sometimes called twice
                }

                if ($entityName === 'postmeta' && !isset($where['vp_post_id'])) {
                    $vpIdTable = $this->dbSchemaInfo->getPrefixedTableName('vp_id');
                    $postMetaTable = $this->dbSchemaInfo->getPrefixedTableName('postmeta');

                    $where['vp_post_id'] = $this->get_var("SELECT HEX(vp_id) FROM $vpIdTable WHERE `table` = 'posts' AND ID = (SELECT post_id FROM $postMetaTable WHERE meta_id = $id)");
                }

                if ($entityName === 'usermeta' && !isset($where['vp_user_id'])) {
                    $vpIdTable = $this->dbSchemaInfo->getPrefixedTableName('vp_id');
                    $userMetaTable = $this->dbSchemaInfo->getPrefixedTableName('usermeta');

                    $where['vp_user_id'] = $this->get_var("SELECT HEX(vp_id) FROM $vpIdTable WHERE `table` = 'users' AND ID = (SELECT user_id FROM $userMetaTable WHERE umeta_id = $id)");
                }

                $this->deleteId($entityName, $id);
                $this->mirror->delete($entityName, $where);
            }

            return $result;
        }

        $this->mirror->delete($entityName, $where);
        return $result;
    }

    private function stripTablePrefix($tableName) {
        return substr($tableName, strlen($this->prefix));
    }

    private function saveId($entityName, $id, $vpId) {
        $vpIdTableName = $this->getVpIdTableName();
        $tableName = $this->dbSchemaInfo->getTableName($entityName);
        $query = "INSERT INTO $vpIdTableName (`vp_id`, `table`, `id`) VALUES (UNHEX('$vpId'), \"$tableName\", $id)";
        $this->query($query);
    }

    private function deleteId($entityName, $id) {
        $vpIdTableName = $this->getVpIdTableName();
        $tableName = $this->dbSchemaInfo->getTableName($entityName);
        $deleteQuery = "DELETE FROM $vpIdTableName WHERE `table` = \"$tableName\" AND id = $id";
        $this->query($deleteQuery);
    }

    private function getVpIdTableName() {
        return $this->dbSchemaInfo->getPrefixedTableName('vp_id');
    }

    private function generateId() {
        return IdUtil::newId();
    }

    private function fillId($entityName, $data, $id) {
        $idColumnName = $this->dbSchemaInfo->getEntityInfo($entityName)->idColumnName;
        if (!isset($data[$idColumnName])) {
            $data[$idColumnName] = $id;
        }
        return $data;
    }

    private function getVpId($entityName, $id) {
        $vpIdTableName = $this->getVpIdTableName();
        $tableName = $this->dbSchemaInfo->getTableName($entityName);
        $getVpIdSql = "SELECT HEX(vp_id) FROM $vpIdTableName WHERE `table` = \"$tableName\" AND id = $id";
        return $this->get_var($getVpIdSql);
    }

    private function getUsermetaId($user_id, $meta_key) {
        $getMetaIdSql = "SELECT umeta_id FROM {$this->prefix}usermeta WHERE meta_key = \"$meta_key\" AND user_id = $user_id";
        return $this->get_var($getMetaIdSql);
    }

    private function getPostMetaId($post_id, $meta_key) {
        $getMetaIdSql = "SELECT meta_id FROM {$this->prefix}postmeta WHERE meta_key = \"$meta_key\" AND post_id = $post_id";
        return $this->get_var($getMetaIdSql);
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
        $ids = $this->get_col($this->prepare($sql, $where));
        return $ids;
    }

    private function replaceForeignKeysWithReferences($entityName, $entity) {
        $references = $this->dbSchemaInfo->getEntityInfo($entityName)->references;
        $vpIdTable = $this->dbSchemaInfo->getPrefixedTableName('vp_id');

        foreach ($references as $referenceName => $targetEntity) {
            $targetTable = $this->dbSchemaInfo->getEntityInfo($targetEntity)->tableName;

            if (isset($entity[$referenceName]) && $entity[$referenceName] > 0) {
                $referenceVpId = $this->get_var("SELECT HEX(vp_id) FROM $vpIdTable WHERE `table` = '$targetTable' AND id=$entity[$referenceName]");
                $entity['vp_' . $referenceName] = $referenceVpId;
            }

            unset($entity[$referenceName]);
        }

        return $entity;
    }
}
