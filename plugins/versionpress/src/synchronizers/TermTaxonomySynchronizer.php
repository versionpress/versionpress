<?php
class TermTaxonomySynchronizer extends SynchronizerBase {
    private $schema;

    function __construct(Storage $storage, wpdb $database, DbSchemaInfo $dbSchema) {
        parent::__construct($storage, $database, $dbSchema, 'term_taxonomy');
        $this->schema = $dbSchema;
    }

    protected function transformEntities($entities) {
        return $this->extractTaxonomiesFromTerms($entities);
    }

    private function extractTaxonomiesFromTerms($terms) {
        $taxonomies = array();

        foreach($terms as $term) {
            if(!isset($term['taxonomies'])) continue;

            foreach($term['taxonomies'] as $taxonomyId => $taxonomy) {
                $copy = $taxonomy;
                $copy[$this->schema->getEntityInfo('term_taxonomy')->idColumnName] = $taxonomyId;
                $copy['vp_term_id'] = $term['vp_id'];
                $taxonomies[] = $copy;
            }
        }

        return $taxonomies;
    }
}