<?php

class TermRelationshipsSynchronizer implements Synchronizer {

    /**
     * @var PostStorage
     */
    private $postStorage;
    private $postIdColumnName;
    /**
     * @var ExtendedWpdb
     */
    private $database;
    /**
     * @var DbSchemaInfo
     */
    private $dbSchema;

    function __construct(Storage $postStorage, ExtendedWpdb $database, DbSchemaInfo $dbSchema) {
        $this->postStorage = $postStorage;
        $this->postIdColumnName = $dbSchema->getEntityInfo('posts')->idColumnName;
        $this->database = $database;
        $this->dbSchema = $dbSchema;
    }

    function synchronize() {
        $entities = $this->loadEntitiesFromStorage();
        $this->truncateTable();
        $this->fillTable($entities);
    }

    /**
     * Transforms $post from
     *   $post[vp_id => x, category => y, post_tag => z] (object saved in file)
     * to
     *   $relationship[vp_object_id => x, vp_term_taxonomy_id => y]
     *   $relationship[vp_object_id => x, vp_term_taxonomy_id => z] (entries in DB)
     */
    private function transformEntities($entities) {
        $relationships = array();

        foreach($entities as $post) {
            if(isset($post['category']))
                foreach($post['category'] as $category)
                    $relationships[] = array(
                        'vp_object_id' => $post['vp_id'],
                        'vp_term_taxonomy_id' => $category
                    );
            if(isset($post['post_tag']))
                foreach($post['post_tag'] as $tag)
                    $relationships[] = array(
                        'vp_object_id' => $post['vp_id'],
                        'vp_term_taxonomy_id' => $tag
                    );
        }

        return $relationships;
    }

    private function loadEntitiesFromStorage() {
        return $this->transformEntities($this->postStorage->loadAll());
    }

    private function getVpIdsMap($entities) {
        $vpIds = array();
        foreach($entities as $entity) {
            $vpIds[] = $entity['vp_object_id'];
            $vpIds[] = $entity['vp_term_taxonomy_id'];
        }

        $hexVpIds = array_map(function($vpId) { return "UNHEX('" . $vpId  . "')"; }, $vpIds);

        return $this->database->get_results('SELECT HEX(vp_id), id FROM ' . $this->dbSchema->getPrefixedTableName('vp_id') . ' WHERE vp_id IN (' . join(', ', $hexVpIds) . ')', ARRAY_MAP);
    }

    private function truncateTable() {
        $this->database->query('TRUNCATE TABLE ' . $this->dbSchema->getPrefixedTableName('term_relationships'));
    }

    private function fillTable($entities) {
        $vpIdsMap = $this->getVpIdsMap($entities);
        $sql = 'INSERT INTO ' . $this->dbSchema->getPrefixedTableName('term_relationships') . ' (object_id, term_taxonomy_id) VALUES ';
        $valuesSql = array();
        foreach($entities as $entity) {
            $valuesSql[] = "(" . $vpIdsMap[$entity['vp_object_id']] . ", " . $vpIdsMap[$entity['vp_term_taxonomy_id']] . ")";
        }
        $sql .= join(', ', $valuesSql);

        $this->database->query($sql);
    }
}