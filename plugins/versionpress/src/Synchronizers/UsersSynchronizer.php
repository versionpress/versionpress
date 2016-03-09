<?php
namespace VersionPress\Synchronizers;

use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\ShortcodesReplacer;
use VersionPress\Storages\Storage;
use VersionPress\Utils\AbsoluteUrlReplacer;
use VersionPress\Utils\WordPressCacheUtils;
use wpdb;

/**
 * Users synchronizer, does quite strict filtering of entity content (only allows
 * a couple of properties to be set).
 */
class UsersSynchronizer extends SynchronizerBase {
    function __construct(Storage $storage, $wpdb, DbSchemaInfo $dbSchema, AbsoluteUrlReplacer $urlReplacer, ShortcodesReplacer $shortcodesReplacer) {
        parent::__construct($storage, $wpdb, $dbSchema, $urlReplacer, $shortcodesReplacer, 'user');
    }

    protected function doEntitySpecificActions() {
        parent::doEntitySpecificActions();
        $this->clearCache();
    }


    private function clearCache() {
        WordPressCacheUtils::clearUserCache(array_column($this->entities, 'vp_id'), $this->database);
    }
}
