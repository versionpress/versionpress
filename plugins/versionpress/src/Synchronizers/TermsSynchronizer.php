<?php

namespace VersionPress\Synchronizers;

use VersionPress\Database\DbSchemaInfo;
use VersionPress\Storages\Storage;
use VersionPress\Utils\AbsoluteUrlReplacer;
use VersionPress\Utils\WordPressCacheUtils;
use wpdb;

class TermsSynchronizer extends SynchronizerBase {

    private $dbSchema;

    function __construct(Storage $storage, $wpdb, DbSchemaInfo $dbSchema, AbsoluteUrlReplacer $urlReplacer) {
        parent::__construct($storage, $wpdb, $dbSchema, $urlReplacer, 'term');
        $this->dbSchema = $dbSchema;
    }

    protected function transformEntities($entities) {
        $entities = parent::transformEntities($entities);
        foreach ($entities as $id => &$entity) {
            unset($entity['taxonomies']); // taxonomies are synchronized by VersionPress\Synchronizers\TermTaxonomiesSynchronizer
        }
        return $entities;
    }

    protected function doEntitySpecificActions() {
        parent::doEntitySpecificActions();
        WordPressCacheUtils::clearTermCache(array_column($this->entities, 'vp_id'), $this->database);
    }
}
