<?php

class UserStorage extends SingleFileStorage implements EntityStorage {

    function __construct($file) {
        parent::__construct($file, 'user', 'ID');
    }

    /**
     * @param $entity
     * @param $changeType
     * @return EntityChangeInfo
     */
    protected function createChangeInfo($entity, $changeType) {
        global $wpdb, $table_prefix;
        $login = $entity["user_login"];
        return new UserChangeInfo($changeType, $entity, $login);
    }
}