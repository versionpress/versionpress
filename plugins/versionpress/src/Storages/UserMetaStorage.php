<?php

namespace VersionPress\Storages;

use Nette\Utils\Strings;
use VersionPress\ChangeInfos\UserMetaChangeInfo;
use VersionPress\Utils\ArrayUtils;

class UserMetaStorage extends MetaEntityStorage {

    function __construct(UserStorage $userStorage) {
        parent::__construct($userStorage, 'meta_key', 'meta_value', 'vp_user_id');
    }

    public function shouldBeSaved($data) {
        if ($this->keyEquals($data, 'session_tokens') ||
            $this->keyEquals($data, 'nav_menu_recently_edited')) {
            return false;
        }

        if ($this->keyEndsWith($data, 'dashboard_quick_press_last_post_id')) {
            return false;
        }

        return parent::shouldBeSaved($data);
    }

    private function keyEquals($data, $key) {
        return (isset($data['meta_key']) && $data['meta_key'] === $key);
    }

    private function keyEndsWith($data, $suffix) {
        return (isset($data['meta_key']) && Strings::endsWith($data['meta_key'], $suffix));
    }

    protected function createChangeInfoWithParentEntity($oldEntity, $newEntity, $oldParentEntity, $newParentEntity, $action) {
        $userMetaVpId = ArrayUtils::getFieldFromFirstWhereExists('vp_id', $oldEntity, $newEntity);
        $userLogin = ArrayUtils::getFieldFromFirstWhereExists('user_login', $oldParentEntity, $newParentEntity);
        $userMetaKey = ArrayUtils::getFieldFromFirstWhereExists('meta_key', $oldEntity, $newEntity);
        $userVpId = ArrayUtils::getFieldFromFirstWhereExists('vp_id', $oldParentEntity, $newParentEntity);

        return new UserMetaChangeInfo($action, $userMetaVpId, $userLogin, $userMetaKey, $userVpId);
    }
}
