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
        $result = parent::insert($table, $data, $format);

        $entityName = $this->stripTablePrefix($table);
        $shouldBeSaved = $this->mirror->shouldBeSaved($entityName, $data);

        if (!$shouldBeSaved)
            return $result;

        $shouldHaveId = $this->dbSchemaInfo->hasId($entityName);

        if ($shouldHaveId) {
            $data['vp_id'] = $this->generateVpId();
            $this->saveId($entityName, $this->insert_id, $data['vp_id']);
        }

        $hasReferences = $this->dbSchemaInfo->hasReferences($entityName);

        if ($hasReferences) {
            $references = $this->dbSchemaInfo->getReferences($entityName);
            foreach ($references as $referenceName => $referenceInfo) {
                if (isset($data[$referenceName]) && $data[$referenceName] > 0)
                    $this->saveReference($entityName, $referenceName, $data['vp_id'], $data[$referenceName]);
            }
        }

        $this->mirror->save($entityName, $data, array(), $this->insert_id);
        return $result;
    }

    function update($table, $data, $where, $format = null, $where_format = null) {
        $result = parent::update($table, $data, $where, $format, $where_format);
        $entityName = $this->stripTablePrefix($table);

        $shouldBeSaved = $this->mirror->shouldBeSaved($entityName, $data);
        if(!$shouldBeSaved)
            return $result;

        $shouldHaveId = $this->dbSchemaInfo->hasId($entityName);

        if($shouldHaveId) {
            $id = $where[$this->dbSchemaInfo->getIdColumnName($entityName)];
            $hasId = $this->hasId($entityName, $id);

            if (!$hasId) {
                $data['vp_id'] = $this->generateVpId();
                $this->saveId($entityName, $id, $data['vp_id']);
            }
        }

        $hasReferences = $this->dbSchemaInfo->hasReferences($entityName);

        if ($hasReferences) {
            $references = $this->dbSchemaInfo->getReferences($entityName);
            foreach ($references as $referenceName => $referenceInfo) {
                if (isset($data[$referenceName]) && $data[$referenceName] > 0)
                    $this->saveReference($entityName, $referenceName, $data['vp_id'], $data[$referenceName]);
            }
        }

        $this->mirror->save($entityName, $data, $where);
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

    private function addTablePrefix($entityName) {
        global $table_prefix;
        return $table_prefix . $entityName;
    }

    private function generateVpId() {
        return hexdec(uniqid());
    }

    private function saveId($entityName, $id, $vpId) {
        $vpIdTableName = $this->getVpIdTableName();
        $query = "INSERT INTO $vpIdTableName (`vp_id`, `table`, `id`) VALUES ($vpId, \"$entityName\", $id)";
        $this->query($query);
    }

    private function saveReference($entityName, $referenceName, $vpId, $id) {
        $vpIdTableName = $this->getVpIdTableName();
        $reference = $this->dbSchemaInfo->getReference($entityName, $referenceName);
        $getReferenceIdSql = "SELECT vp_id FROM $vpIdTableName WHERE `table` = \"$reference[table]\" AND id = $id";
        $referenceId = $this->get_var($getReferenceIdSql);

        if ($referenceId === null)
            return;

        $this->creteReferenceRecord($entityName, $referenceName, $vpId, $referenceId);
    }

    private function hasId($entityName, $id) {
        $vpIdTableName = $this->getVpIdTableName();
        $query = "SELECT * FROM $vpIdTableName WHERE `table` = \"$entityName\" AND id = $id";
        $result = $this->get_row($query);
        return (bool)$result;
    }

    private function getVpIdTableName() {
        return $this->addTablePrefix('vp_id');
    }

    private function getVpReferenceTableName() {
        return $this->addTablePrefix('vp_references');
    }

    private function creteReferenceRecord($entityName, $referenceName, $vpId, $referenceId) {
        $vpReferenceTableName = $this->getVpReferenceTableName();

        $query = "INSERT INTO $vpReferenceTableName (`table`, `reference`, `vp_id`, `reference_vp_id`)
                    VALUES (\"$entityName\", \"$referenceName\", $vpId, $referenceId)
                    ON DUPLICATE KEY UPDATE `reference_vp_id` = $referenceId";
        $this->query($query);
    }
}