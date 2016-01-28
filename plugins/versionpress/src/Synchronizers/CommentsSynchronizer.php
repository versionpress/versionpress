<?php
namespace VersionPress\Synchronizers;

use VersionPress\Database\DbSchemaInfo;
use VersionPress\Storages\Storage;
use VersionPress\Utils\AbsoluteUrlReplacer;
use VersionPress\Utils\WordPressCacheUtils;
use wpdb;

/**
 * Comments synchronizer, the simplest VPID one (simply uses base class implementation)
 */
class CommentsSynchronizer extends SynchronizerBase {
    function __construct(Storage $storage, $wpdb, DbSchemaInfo $dbSchema, AbsoluteUrlReplacer $urlReplacer) {
        parent::__construct($storage, $wpdb, $dbSchema, $urlReplacer, 'comment');
    }

    protected function doEntitySpecificActions() {
        parent::doEntitySpecificActions();
        $this->clearCache();
    }

    private function clearCache() {
        WordPressCacheUtils::clearCommentCache(array_column($this->entities, 'vp_id'), $this->database);
        WordPressCacheUtils::clearPOSTCache(array_column($this->entities, 'vp_post_id'), $this->database);
    }
}
