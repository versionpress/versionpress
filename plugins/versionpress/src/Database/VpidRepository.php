<?php

namespace VersionPress\Database;

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
}