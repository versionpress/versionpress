<?php

namespace VersionPress\Storages;

use VersionPress\ChangeInfos\UserChangeInfo;

class UserStorage extends DirectoryStorage {

    protected function createChangeInfo($oldEntity, $newEntity, $action = null) {
        return new UserChangeInfo($action, $newEntity["vp_id"], $newEntity["user_login"]);
    }

    protected function removeUnwantedColumns($entity) {
        static $excludeList = array('user_activation_key');
        foreach ($excludeList as $excludeKey) {
            unset($entity[$excludeKey]);
        }

        return $entity;
    }
}
