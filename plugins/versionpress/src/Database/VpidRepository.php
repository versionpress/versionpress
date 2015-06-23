<?php

namespace VersionPress\Database;

use VersionPress\Utils\IdUtil;
use VersionPress\Utils\ReferenceUtils;
use wpdb;

class VpidRepository {
    /** @var wpdb */
    private $database;
    /** @var DbSchemaInfo */
    private $schemaInfo;
    /** @var string */
    private $vpidTableName;

    public function __construct(wpdb $database, DbSchemaInfo $schemaInfo) {
        $this->database = $database;
        $this->schemaInfo = $schemaInfo;
        $this->vpidTableName = $schemaInfo->getPrefixedTableName('vp_id');
    }

    /**
     * Returns VPID of entity of given type and id.
     *
     * @param $entityName
     * @param $id
     * @return null|string
     */
    public function getVpidForEntity($entityName, $id) {
        $tableName = $this->schemaInfo->getTableName($entityName);
        return $this->database->get_var("SELECT HEX(vp_id) FROM $this->vpidTableName WHERE id = $id AND `table` = '$tableName'");
    }

    public function replaceForeignKeysWithReferences($entityName, $entity) {
        $entityInfo = $this->schemaInfo->getEntityInfo($entityName);
        $vpIdTable = $this->schemaInfo->getPrefixedTableName('vp_id');

        foreach ($entityInfo->references as $referenceName => $targetEntity) {
            $targetTable = $this->schemaInfo->getEntityInfo($targetEntity)->tableName;

            if (isset($entity[$referenceName]) && $entity[$referenceName] > 0) {
                $referenceVpId = $this->database->get_var("SELECT HEX(vp_id) FROM $vpIdTable WHERE `table` = '$targetTable' AND id=$entity[$referenceName]");
                $entity['vp_' . $referenceName] = $referenceVpId;
            }

            unset($entity[$referenceName]);
        }

        foreach ($entityInfo->valueReferences as $referenceName => $targetEntity) {
            $targetTable = $this->schemaInfo->getEntityInfo($targetEntity)->tableName;
            list($sourceColumn, $sourceValue, $valueColumn) = array_values(ReferenceUtils::getValueReferenceDetails($referenceName));

            if (isset($entity[$sourceColumn]) && $entity[$sourceColumn] == $sourceValue && isset($entity[$valueColumn]) && $entity[$valueColumn] > 0) {
                $referenceVpId = $this->database->get_var("SELECT HEX(vp_id) FROM $vpIdTable WHERE `table` = '$targetTable' AND id=".$entity[$valueColumn]);
                $entity[$valueColumn] = $referenceVpId;
            }
        }

        return $entity;
    }

    public function identifyEntity($entityName, $data, $id) {
        if ($this->schemaInfo->getEntityInfo($entityName)->usesGeneratedVpids) {
            $data['vp_id'] = IdUtil::newId();
            $this->saveId($entityName, $id, $data['vp_id']);
        }

        $data[$this->schemaInfo->getEntityInfo($entityName)->idColumnName] = $id;

        $data = $this->fillId($entityName, $data, $id);
        return $data;
    }

    public function deleteId($entityName, $id) {
        $vpIdTableName = $this->schemaInfo->getPrefixedTableName('vp_id');
        $tableName = $this->schemaInfo->getTableName($entityName);
        $deleteQuery = "DELETE FROM $vpIdTableName WHERE `table` = \"$tableName\" AND id = $id";
        $this->database->query($deleteQuery);
    }

    private function saveId($entityName, $id, $vpId) {
        $vpIdTableName = $this->schemaInfo->getPrefixedTableName('vp_id');
        $tableName = $this->schemaInfo->getTableName($entityName);
        $query = "INSERT INTO $vpIdTableName (`vp_id`, `table`, `id`) VALUES (UNHEX('$vpId'), \"$tableName\", $id)";
        $this->database->query($query);
    }

    private function fillId($entityName, $data, $id) {
        $idColumnName = $this->schemaInfo->getEntityInfo($entityName)->idColumnName;
        if (!isset($data[$idColumnName])) {
            $data[$idColumnName] = $id;
        }
        return $data;
    }
}