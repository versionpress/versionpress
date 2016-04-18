<?php

namespace VersionPress\Synchronizers;

use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\ShortcodesReplacer;
use VersionPress\Storages\Storage;
use VersionPress\Utils\AbsoluteUrlReplacer;
use VersionPress\Utils\WordPressCacheUtils;
use wpdb;

class TermTaxonomiesSynchronizer extends SynchronizerBase {

    function synchronize($task, $entitiesToSynchronize = null) {
        $this->database->query("drop index term_id_taxonomy on {$this->database->term_taxonomy}");
        $result = parent::synchronize($task, $entitiesToSynchronize);
        $this->database->query("create unique index term_id_taxonomy on {$this->database->term_taxonomy}(term_id, taxonomy)");
        return $result;
    }

    protected function doEntitySpecificActions() {
        WordPressCacheUtils::clearTermCache(array_column($this->entities, 'vp_term_id'), $this->database);
        return true;
    }

    /**
     * @param Database $database
     */
    public static function fixPostsCount($database) {
        $sql = "update {$database->term_taxonomy} tt set tt.count =
          (select count(*) from {$database->term_relationships} tr where tr.term_taxonomy_id = tt.term_taxonomy_id);";
        $database->query($sql);
    }

}
