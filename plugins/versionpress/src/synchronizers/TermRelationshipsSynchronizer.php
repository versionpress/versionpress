<?php
namespace VersionPress\Synchronizers;

use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\ExtendedWpdb;
use VersionPress\Storages\PostStorage;
use VersionPress\Storages\Storage;

/**
 * Terms relationships synchronizer. Completely truncates the table and fills
 * it with data from the posts storage.
 */
class TermRelationshipsSynchronizer implements Synchronizer {

    /** @var PostStorage */
    private $postStorage;

    /** @var ExtendedWpdb */
    private $database;

    /** @var DbSchemaInfo */
    private $dbSchema;

    function __construct(Storage $postStorage, ExtendedWpdb $database, DbSchemaInfo $dbSchema) {
        $this->postStorage = $postStorage;
        $this->database = $database;
        $this->dbSchema = $dbSchema;
    }

    function synchronize() {
        $entities = $this->transformEntities($this->postStorage->loadAll());
        $this->truncateTable();
        $this->fillTable($entities);
    }

    /**
     * Transforms $post from
     *   $post[vp_id => x, category => y, post_tag => z] (object saved in file)
     * to
     *   $relationship[vp_object_id => x, vp_term_taxonomy_id => y]
     *   $relationship[vp_object_id => x, vp_term_taxonomy_id => z] (entries in DB)
     *
     * @param array $entities Entities as returned by VersionPress\Storages\PostStorage
     * @return array Entities suitable for the term_relationships table
     */
    private function transformEntities($entities) {
        $relationships = array();

        foreach ($entities as $post) {
            if (isset($post['category']))
                foreach ($post['category'] as $category)
                    $relationships[] = array(
                        'vp_object_id' => $post['vp_id'],
                        'vp_term_taxonomy_id' => $category
                    );
            if (isset($post['post_tag']))
                foreach ($post['post_tag'] as $tag)
                    $relationships[] = array(
                        'vp_object_id' => $post['vp_id'],
                        'vp_term_taxonomy_id' => $tag
                    );
        }

        return $relationships;
    }

    private function truncateTable() {
        $this->database->query('TRUNCATE TABLE ' . $this->dbSchema->getPrefixedTableName('term_relationships'));
    }

    /**
     * @param array $entities Array of $relationship[vp_object_id => x, vp_term_taxonomy_id => y]
     */
    private function fillTable($entities) {
        $vpIdsMap = $this->getVpidsMap($entities);
        $sql = 'INSERT INTO ' . $this->dbSchema->getPrefixedTableName('term_relationships') . ' (object_id, term_taxonomy_id) VALUES ';
        $valuesSql = array();
        foreach ($entities as $entity) {
            $valuesSql[] = "(" . $vpIdsMap[$entity['vp_object_id']] . ", " . $vpIdsMap[$entity['vp_term_taxonomy_id']] . ")";
        }
        $sql .= join(', ', $valuesSql);

        $this->database->query($sql);
    }

    /**
     * @param array $entities Array of $relationship[vp_object_id => x, vp_term_taxonomy_id => y]
     * @return array|mixed Map between vp_id and wordpress_id
     */
    private function getVpidsMap($entities) {
        $vpIds = array();
        foreach ($entities as $entity) {
            $vpIds[] = $entity['vp_object_id'];
            $vpIds[] = $entity['vp_term_taxonomy_id'];
        }

        $hexVpIds = array_map(function ($vpId) {
            return "UNHEX('" . $vpId . "')";
        }, $vpIds);

        return $this->database->get_results('SELECT HEX(vp_id), id FROM ' . $this->dbSchema->getPrefixedTableName('vp_id') . ' WHERE vp_id IN (' . join(', ', $hexVpIds) . ')', ARRAY_MAP);
    }

}
