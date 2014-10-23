<?php

class PostsSynchronizer extends SynchronizerBase {
    /** @var EntityFilter */
    private $filter;

    function __construct(EntityStorage $storage, wpdb $database, DbSchemaInfo $dbSchema) {
        parent::__construct($storage, $database, $dbSchema, 'posts');
        $this->filter = new AbsoluteUrlFilter();
    }

    protected function filterEntities($entities) {
        $filteredEntities = array();

        foreach ($entities as $entity) {
            $entityClone = $entity;
            unset($entityClone['category'], $entityClone['post_tag']); // categories and tags are synchronized by TermRelationshipsSynchronizer
            $entityClone = $this->filter->restore($entityClone);
            $filteredEntities[] = $entityClone;
        }

        return parent::filterEntities($filteredEntities);
    }
}