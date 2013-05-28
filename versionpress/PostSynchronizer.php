<?php

class PostSynchronizer extends PostsAndCommentsBaseSynchronizer {

    function __construct(EntityStorage $storage, wpdb $database, $tableName) {
        parent::__construct($storage, $database, $tableName, 'ID', 'post_parent');
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