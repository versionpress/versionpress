<?php

class UserStorage extends SingleFileStorage {

    function __construct($file) {
        parent::__construct($file, 'user', 'ID');
    }

    protected function createChangeInfo($oldEntity, $newEntity, $action = null) {
        return new UserChangeInfo($action, $newEntity["vp_id"], $newEntity["user_login"]);
    }
}