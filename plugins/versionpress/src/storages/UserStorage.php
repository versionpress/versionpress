<?php

class UserStorage extends SingleFileStorage implements EntityStorage {

    function __construct($file) {
        parent::__construct($file, 'user', 'ID');
    }

    /**
     * @param $entityId
     * @param $changeType
     * @return EntityChangeInfo
     */
    protected function createChangeInfo($entityId, $changeType) {
        // TODO: Implement createChangeInfo() method.
    }
}