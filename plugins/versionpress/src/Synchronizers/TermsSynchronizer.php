<?php

namespace VersionPress\Synchronizers;

use VersionPress\Database\DbSchemaInfo;
use VersionPress\Storages\Storage;
use wpdb;

class TermsSynchronizer extends SynchronizerBase {

    private $dbSchema;

    function __construct(Storage $storage, $wpdb, DbSchemaInfo $dbSchema) {
        parent::__construct($storage, $wpdb, $dbSchema, 'term');
        $this->dbSchema = $dbSchema;
    }

    protected function transformEntities($entities) {
        $transformedEntities = array();
        foreach ($entities as $id => $entity) {
            $entityCopy = $entity;
            unset($entityCopy['taxonomies']); // taxonomies are synchronized by VersionPress\Synchronizers\TermTaxonomySynchronizer
            $entityCopy[$this->dbSchema->getEntityInfo('term')->idColumnName] = $id;

            $transformedEntities[] = $entityCopy;
        }
        return $transformedEntities;
    }
}
