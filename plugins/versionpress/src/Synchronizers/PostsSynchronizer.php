<?php
namespace VersionPress\Synchronizers;

use Nette\Utils\Strings;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Utils\AbsoluteUrlReplacer;
use VersionPress\Storages\Storage;
use VersionPress\Utils\WordPressCacheUtils;
use wpdb;

/**
 * Posts synchronizer. Fixes comment counts for restored posts.
 */
class PostsSynchronizer extends SynchronizerBase {

    function __construct(Storage $storage, $wpdb, DbSchemaInfo $dbSchema, AbsoluteUrlReplacer $urlReplacer) {
        parent::__construct($storage, $wpdb, $dbSchema, $urlReplacer, 'post');
    }

    protected function doEntitySpecificActions() {
        if ($this->passNumber == 1) {
            return false;
        }

        $this->fixCommentCounts();
        $this->clearCache();
        return true;
    }

    private function fixCommentCounts() {
        $sql = "update {$this->database->prefix}posts set comment_count =
     (select count(*) from {$this->database->prefix}comments where comment_post_ID = {$this->database->prefix}posts.ID and comment_approved = 1);";
        $this->database->query($sql);
    }

    private function clearCache() {
        WordPressCacheUtils::clearPostCache(array_column($this->entities, 'vp_id'), $this->database);
    }
}
