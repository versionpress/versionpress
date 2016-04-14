<?php
namespace VersionPress\Synchronizers;

use Nette\Utils\Strings;
use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\ShortcodesReplacer;
use VersionPress\DI\DIContainer;
use VersionPress\DI\VersionPressServices;
use VersionPress\Utils\AbsoluteUrlReplacer;
use VersionPress\Storages\Storage;
use VersionPress\Utils\WordPressCacheUtils;
use wpdb;

/**
 * Posts synchronizer. Fixes comment counts for restored posts.
 */
class PostsSynchronizer extends SynchronizerBase {

    protected function doEntitySpecificActions() {
        if ($this->passNumber == 1) {
            return false;
        }
        
        $this->clearCache();
        return true;
    }


    public static function fixCommentCounts() {
        $versionPressContainer = DIContainer::getConfiguredInstance();
        /** @var Database $database */
        $database = $versionPressContainer->resolve(VersionPressServices::DATABASE);

        $sql = "update {$database->prefix}posts set comment_count =
     (select count(*) from {$database->prefix}comments where comment_post_ID = {$database->prefix}posts.ID and comment_approved = 1);";
        $database->query($sql);
    }

    private function clearCache() {
        WordPressCacheUtils::clearPostCache(array_column($this->entities, 'vp_id'), $this->database);
    }
}
