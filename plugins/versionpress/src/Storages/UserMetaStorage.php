<?php

namespace VersionPress\Storages;

use Nette\Utils\Strings;
use VersionPress\ChangeInfos\UserMetaChangeInfo;
use VersionPress\Database\EntityInfo;
use VersionPress\Utils\ArrayUtils;

class UserMetaStorage extends MetaEntityStorage {

    /** @var string */
    private $dbPrefix;
    const PREFIX_PLACEHOLDER = "<<table-prefix>>";

    function __construct(UserStorage $userStorage, EntityInfo $entityInfo, $dbPrefix) {
        parent::__construct($userStorage, $entityInfo, 'meta_key', 'meta_value');
        $this->dbPrefix = $dbPrefix;
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
