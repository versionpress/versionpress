<?php

namespace VersionPress\Storages;

use Nette\Utils\Strings;
use VersionPress\ChangeInfos\UserMetaChangeInfo;
use VersionPress\Utils\ArrayUtils;

class UserMetaStorage extends MetaEntityStorage {

    /** @var string */
    private $dbPrefix;
    const PREFIX_PLACEHOLDER = "<<table-prefix>>";

    function __construct(UserStorage $userStorage, $dbPrefix) {
        parent::__construct($userStorage, 'meta_key', 'meta_value', 'vp_user_id');
        $this->dbPrefix = $dbPrefix;
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

    protected function createJoinedKey($key, $vpId) {
        $key = $this->maybeReplacePrefixWithPlaceholder($key);
        return parent::createJoinedKey($key, $vpId);
    }

    protected function splitJoinedKey($key) {
        $splitKey = parent::splitJoinedKey($key);
        $splitKey[$this->keyName] = $this->maybeReplacePlaceholderWithPrefix($splitKey[$this->keyName]);
        return $splitKey;
    }

    protected function createChangeInfoWithParentEntity($oldEntity, $newEntity, $oldParentEntity, $newParentEntity, $action) {
        $userMetaVpId = $newEntity['vp_id'];
        $userLogin = $newParentEntity['user_login'];
        $userMetaKey = $newEntity['meta_key'];
        $userVpId = $newParentEntity['vp_id'];

        return new UserMetaChangeInfo($action, $userMetaVpId, $userLogin, $userMetaKey, $userVpId);
    }

    private function maybeReplacePrefixWithPlaceholder($key) {
        if (Strings::startsWith($key, $this->dbPrefix)) {
            return self::PREFIX_PLACEHOLDER . Strings::substring($key, Strings::length($this->dbPrefix));
        }
        return $key;
    }

    private function maybeReplacePlaceholderWithPrefix($key) {
        if (Strings::startsWith($key, self::PREFIX_PLACEHOLDER)) {
            return $this->dbPrefix . Strings::substring($key, Strings::length(self::PREFIX_PLACEHOLDER));
        }
        return $key;
    }
}
