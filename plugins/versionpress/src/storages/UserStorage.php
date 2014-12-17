<?php

namespace VersionPress\Storages;

use VersionPress\ChangeInfos\UserChangeInfo;

class UserStorage extends SingleFileStorage {

    protected function createChangeInfo($oldEntity, $newEntity, $action = null) {
        return new UserChangeInfo($action, $newEntity["vp_id"], $newEntity["user_login"]);
    }
}