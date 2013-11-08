<?php

class PostsSynchronizer extends SynchronizerBase {
    function __construct(EntityStorage $storage, wpdb $database, DbSchemaInfo $dbSchema) {
        parent::__construct($storage, $database, $dbSchema, 'posts');
    }

    protected function filterEntities($entities) {
        $filteredEntities = array();

        foreach ($entities as $entity) {
            $entityClone = $entity;
            unset($entityClone['category'], $entityClone['post_tag']); // categories and tags are synchronized by TermRelationshipsSynchronizer
            $filteredEntities[] = $entityClone;
        }

        return parent::filterEntities($filteredEntities);
    }
}