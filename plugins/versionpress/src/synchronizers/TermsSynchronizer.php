<?php

class TermsSynchronizer extends SynchronizerBase {

    private $dbSchema;

    function __construct(Storage $storage, wpdb $database, DbSchemaInfo $dbSchema) {
        parent::__construct($storage, $database, $dbSchema, 'terms');
        $this->dbSchema = $dbSchema;
    }

    protected function transformEntities($entities) {
        $transformedEntities = array();
        foreach ($entities as $id => $entity) {
            $entityCopy = $entity;
            unset($entityCopy['taxonomies']); // taxonomies are synchronized by TermTaxonomySynchronizer
            $entityCopy[$this->dbSchema->getEntityInfo('terms')->idColumnName] = $id;

            $transformedEntities[] = $entityCopy;
        }
        return $transformedEntities;
    }
}
