<?php

class MirroringDatabase extends wpdb {

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
        $entityName = $this->stripTablePrefix($table);
        if ($this->dbSchemaInfo->entityShouldHaveVersionPressId($entityName)) {
            $data = $this->extendDataWithVpId($data);
        }

        if ($this->dbSchemaInfo->isHierarchical($entityName)) {
            $data = $this->extendDataWithVpParentId($entityName, $data);
        }

        $result = parent::insert($table, $data, $format);
        $this->mirror->save($entityName, $data, array(), $this->insert_id);
        return $result;
    }

    function update($table, $data, $where, $format = null, $where_format = null) {
        $entityName = $this->stripTablePrefix($table);

        if ($this->dbSchemaInfo->isHierarchical($entityName)) {
            $data = $this->extendDataWithVpParentId($entityName, $data);
        }

        $result = parent::update($table, $data, $where, $format, $where_format);
        $this->mirror->save($this->stripTablePrefix($table), $data, $where);
        return $result;
    }

    function delete($table, $where, $where_format = null) {
        $result = parent::delete($table, $where, $where_format);
        $this->mirror->delete($this->stripTablePrefix($table), $where);
        return $result;
    }

    private function stripTablePrefix($tableName) {
        global $table_prefix;
        return substr($tableName, strlen($table_prefix));
    }

    private function extendDataWithVpId($data) {
        $data['vp_id'] = hexdec(uniqid());
        return $data;
    }

    private function extendDataWithVpParentId($tableName, $data) {
        global $table_prefix;
        $parentIdColumnName = $this->dbSchemaInfo->getParentIdColumnName($tableName);
        if (isset($data[$parentIdColumnName]) && $data[$parentIdColumnName] != 0) {

            $idColumnName = $this->dbSchemaInfo->getIdColumnName($tableName);
            $parent = $this->get_row("SELECT vp_id FROM " . $table_prefix . $tableName ." WHERE $idColumnName = $data[$parentIdColumnName]");
            $parentVpId = $parent->vp_id;
            $data['vp_parent_id'] = $parentVpId;
        }

        if ($tableName === 'term_taxonomy' && isset($data['term_id'])) { // TODO: Find better solution
            $parentTerm = $this->get_row('SELECT vp_id FROM ' . $table_prefix . 'terms WHERE term_id = ' . $data['term_id']);
            $data['vp_term_id'] = $parentTerm->vp_id;
        }

        return $data;
    }
}