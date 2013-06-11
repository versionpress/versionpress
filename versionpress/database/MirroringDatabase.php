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
        $id = $this->insert_id;
        $entityName = $this->stripTablePrefix($table);
        $shouldBeSaved = $this->mirror->shouldBeSaved($entityName, $data);

        if (!$shouldBeSaved)
            return $result;

        $shouldHaveId = $this->dbSchemaInfo->hasId($entityName);

        if ($shouldHaveId) {
            $data['vp_id'] = $this->generateVpId();
            $this->saveId($entityName, $id, $data['vp_id']);
        }

        $hasReferences = $this->dbSchemaInfo->hasReferences($entityName);

        if ($hasReferences) {
            $data = $this->saveReferences($entityName, $data);
        }

        $this->mirror->save($entityName, $data, array(), $id);

        $this->insert_id = $id; // it was reset by saving id and references
        return $result;
    }

    function update($table, $data, $where, $format = null, $where_format = null) {
        $result = parent::update($table, $data, $where, $format, $where_format);
        $entityName = $this->stripTablePrefix($table);

        $shouldBeSaved = $this->mirror->shouldBeSaved($entityName, $data);
        if (!$shouldBeSaved)
            return $result;

        $shouldHaveId = $this->dbSchemaInfo->hasId($entityName);

        if ($shouldHaveId) {
            $id = $where[$this->dbSchemaInfo->getIdColumnName($entityName)];
            $hasId = $this->hasId($entityName, $id);

            if (!$hasId) {
                $data['vp_id'] = $this->generateVpId();
                $this->saveId($entityName, $id, $data['vp_id']);
            }
        }

        $hasReferences = $this->dbSchemaInfo->hasReferences($entityName);

        if ($hasReferences) {
            $data = $this->saveReferences($entityName, $data);
        }

        $this->mirror->save($entityName, $data, $where);
        return $result;
    }

    function delete($table, $where, $where_format = null) {
        $result = parent::delete($table, $where, $where_format);

        $entityName = $this->stripTablePrefix($table);
        $id = $where[$this->dbSchemaInfo->getIdColumnName($entityName)];

        $hasReferences = $this->dbSchemaInfo->hasReferences($entityName);

        if ($hasReferences) {
            $this->deleteReferences($entityName, $id);
        }

        $hasId = $this->dbSchemaInfo->hasId($entityName);

        if ($hasId) {
            $this->deleteId($entityName, $id);
        }

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
        $referenceId = $this->getReferenceId($entityName, $referenceName, $id);

        if ($referenceId === null)
            return;

        $this->creteReferenceRecord($entityName, $referenceName, $vpId, $referenceId);
        return $referenceId;
    }

    private function deleteReferences($entityName, $id) {
        $vpIdTableName = $this->getVpIdTableName();
        $referencesTableName = $this->getVpReferenceTableName();
        $vpId = $this->get_var("SELECT vp_id FROM $vpIdTableName WHERE `table` = \"$entityName\" AND id = $id");
        $deleteQuery = "DELETE FROM $referencesTableName WHERE vp_id = $vpId";
        $this->query($deleteQuery);
    }

    private function deleteId($entityName, $id) {
        $vpIdTableName = $this->getVpIdTableName();
        $deleteQuery = "DELETE FROM $vpIdTableName WHERE `table` = \"$entityName\" AND id = $id";
        $this->query($deleteQuery);
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

    private function getReferenceId($entityName, $referenceName, $id) {
        $vpIdTableName = $this->getVpIdTableName();
        $reference = $this->dbSchemaInfo->getReference($entityName, $referenceName);
        $getReferenceIdSql = "SELECT vp_id FROM $vpIdTableName WHERE `table` = \"$reference[table]\" AND id = $id";
        $referenceId = $this->get_var($getReferenceIdSql);
        return $referenceId;
    }

    private function saveReferences($entityName, $data) {
        $references = $this->dbSchemaInfo->getReferences($entityName);
        foreach ($references as $referenceName => $referenceInfo) {
            if (isset($data[$referenceName]) && $data[$referenceName] > 0) {
                $referenceId = $this->saveReference($entityName, $referenceName, $data['vp_id'], $data[$referenceName]);
                $data['vp_' . $referenceName] = $referenceId;
            }
            unset($data[$referenceName]);
        }
        return $data;
    }
}