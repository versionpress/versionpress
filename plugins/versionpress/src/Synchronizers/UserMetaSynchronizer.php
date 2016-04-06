<?php

namespace VersionPress\Synchronizers;

use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\ShortcodesReplacer;
use VersionPress\Storages\Storage;
use VersionPress\Utils\AbsoluteUrlReplacer;
use VersionPress\Utils\WordPressCacheUtils;

class UserMetaSynchronizer extends SynchronizerBase {

    function __construct(Storage $storage, Database $database, DbSchemaInfo $dbSchema, AbsoluteUrlReplacer $urlReplacer, ShortcodesReplacer $shortcodesReplacer) {
        parent::__construct($storage, $database, $dbSchema, $urlReplacer, $shortcodesReplacer, 'usermeta');
    }

    protected function doEntitySpecificActions() {
        parent::doEntitySpecificActions();
        WordPressCacheUtils::clearUserCache(array_column($this->entities, 'vp_user_id'), $this->database);
    }
}
