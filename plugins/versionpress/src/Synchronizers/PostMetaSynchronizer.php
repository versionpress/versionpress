<?php

namespace VersionPress\Synchronizers;

use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\ShortcodesReplacer;
use VersionPress\Storages\Storage;
use VersionPress\Utils\AbsoluteUrlReplacer;
use VersionPress\Utils\WordPressCacheUtils;
use wpdb;

class PostMetaSynchronizer extends SynchronizerBase {
    function __construct(Storage $storage, $wpdb, DbSchemaInfo $dbSchema, AbsoluteUrlReplacer $urlReplacer, ShortcodesReplacer $shortcodesReplacer) {
        parent::__construct($storage, $wpdb, $dbSchema, $urlReplacer, $shortcodesReplacer, 'postmeta');
    }

    protected function doEntitySpecificActions() {
        parent::doEntitySpecificActions();
        WordPressCacheUtils::clearPostCache(array_column($this->entities, 'vp_post_id'), $this->database);
    }
}
