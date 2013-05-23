<?php

class PostSynchronizer extends PostsAndCommentsBaseSynchronizer {

    function __construct(EntityStorage $postStorage, wpdb $database, $tableName) {
        parent::__construct($postStorage, $database, $tableName, 'ID');
    }

    protected function doAfterDatabaseUpdate() {
        parent::doAfterDatabaseUpdate();
        $this->fixGuids();
    }

    private function fixGuids() {
        $fixGuidsSql = "UPDATE {$this->tableName} SET guid = IF(LOCATE('=', guid)=0, guid, CONCAT(LEFT(guid, LOCATE('=', guid)), id))";
        $this->database->query($fixGuidsSql);
    }
}