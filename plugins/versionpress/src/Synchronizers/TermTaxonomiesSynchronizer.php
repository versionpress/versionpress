<?php

namespace VersionPress\Synchronizers;

use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\ShortcodesReplacer;
use VersionPress\Storages\Storage;
use VersionPress\Utils\AbsoluteUrlReplacer;
use VersionPress\Utils\WordPressCacheUtils;
use wpdb;

class TermTaxonomiesSynchronizer extends SynchronizerBase {

    function __construct(Storage $storage, $wpdb, DbSchemaInfo $dbSchema, AbsoluteUrlReplacer $urlReplacer, ShortcodesReplacer $shortcodesReplacer) {
        parent::__construct($storage, $wpdb, $dbSchema, $urlReplacer, $shortcodesReplacer, 'term_taxonomy');
    }

    function synchronize($task, $entitiesToSynchronize = null) {
        $this->database->vp_query("drop index term_id_taxonomy on {$this->database->term_taxonomy}");
        $result = parent::synchronize($task, $entitiesToSynchronize);
        $this->database->vp_query("create unique index term_id_taxonomy on {$this->database->term_taxonomy}(term_id, taxonomy)");
        return $result;
    }

    protected function doEntitySpecificActions() {
        if ($this->passNumber == 1) {
            return false;
        }

        $this->fixPostsCount();
        $this->clearCache();
        return true;
    }

    private function fixPostsCount() {
        $sql = "update {$this->database->term_taxonomy} tt set tt.count =
          (select count(*) from {$this->database->term_relationships} tr where tr.term_taxonomy_id = tt.term_taxonomy_id);";
        $this->database->vp_query($sql);
    }

    private function clearCache() {
        WordPressCacheUtils::clearTermCache(array_column($this->entities, 'vp_term_id'), $this->database);
    }
}
