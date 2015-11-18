<?php
namespace VersionPress\Storages;

use VersionPress\ChangeInfos\TermChangeInfo;
use VersionPress\Database\EntityInfo;
use VersionPress\Utils\EntityUtils;
use VersionPress\Utils\IniSerializer;

/**
 * Quite an untypical storage. Stores taxonomy together with terms, as INI sections
 * called <term_vpid>.taxonomies.<term_taxonomy_vpid>.
 *
 * An example of how term-taxonomy is stored:
 *
 *     ; this is a term, just for demo purpose
 *     [8ABB7E35241445A096E60C67977EEA52]
 *     term_id = 1
 *     name = "Uncategorized"
 *     slug = "uncategorized"
 *     term_group = 0
 *     vp_id = "8ABB7E35241445A096E60C67977EEA52"
 *
 *     ; taxonomy of that term:
 *     [8ABB7E35241445A096E60C67977EEA52.taxonomies.B915DEDDA9634BE38367AD6A65D8CA8B]
 *     term_taxonomy_id = 1
 *     taxonomy = "category"
 *     description = ""
 *     vp_id = "B915DEDDA9634BE38367AD6A65D8CA8B"
 */
class TermTaxonomyStorage extends Storage {

    protected $notSavedFields = array('vp_term_id', 'count', 'term_id', 'term_taxonomy_id', 'vp_id');
    /** @var TermStorage */
    private $termStorage;
    /** @var EntityInfo */
    private $entityInfo;

    public function __construct(TermStorage $termStorage, EntityInfo $entityInfo) {
        $this->termStorage = $termStorage;
        $this->entityInfo = $entityInfo;
    }

    public function save($data) {
        $vpid = $data['vp_id'];
        $termVpid = $data['vp_term_id'];
        $term = $this->termStorage->loadEntity($termVpid);

        if (!isset($term['taxonomies']) || !isset($term['taxonomies'][$vpid])) {
            $originalTaxonomy = array();
        } else {
            $originalTaxonomy = $term['taxonomies'][$vpid];
        }

        $newTaxonomy = array_merge($originalTaxonomy, $data);
        foreach ($this->notSavedFields as $field) {
            unset($newTaxonomy[$field]);
        }

        $newTaxonomy = array_filter($newTaxonomy, function ($value) { return $value !== false; });

        if ($newTaxonomy !== $originalTaxonomy) {
            $term['taxonomies'][$vpid] = $newTaxonomy;
            $this->termStorage->save($term);
            return new TermChangeInfo('edit', $termVpid, $term['name'], $newTaxonomy['taxonomy']);
        }

        return null;
    }

    public function delete($restriction) {
        $vpid = $restriction['vp_id'];
        $termVpid = $restriction['vp_term_id'];

        $term = $this->termStorage->loadEntity($termVpid);

        if (!isset($term['taxonomies']) || !isset($term['taxonomies'][$vpid])) {
            return null;
        }

        $originalTaxonomy = $term['taxonomies'][$vpid];
        unset($term['taxonomies'][$vpid]);
        $this->termStorage->save($term);

        return new TermChangeInfo('edit', $termVpid, $term['name'], $originalTaxonomy['taxonomy']);
    }

    public function loadEntity($id, $parentId = null) {
        $term = $this->termStorage->loadEntity($parentId);
        if (!$term) {
            return null;
        }

        if (!isset($term['taxonomies']) || !isset($term['taxonomies'][$id])) {
            return null;
        }

        $taxonomy = $term['taxonomies'][$id];
        $taxonomy['vp_id'] = $id;
        $taxonomy['vp_term_id'] = $parentId;

        return $taxonomy;
    }

    public function loadAll() {
        $terms = $this->termStorage->loadAll();
        $taxonomies = array();

        foreach ($terms as $term) {
            if (!isset($term['taxonomies'])) {
                continue;
            }

            foreach ($term['taxonomies'] as $taxonomyVpid => $taxonomy) {
                $taxonomy['vp_id'] = $taxonomyVpid;
                $taxonomy['vp_term_id'] = $term['vp_id'];
                $taxonomies[$taxonomyVpid] = $taxonomy;
            }
        }

        return $taxonomies;
    }


    public function shouldBeSaved($data) {
        return !(count($data) === 3 && isset($data['count'], $data[$this->entityInfo->idColumnName], $data['vp_id']));
    }

    public function exists($id, $parentId = null) {
        if (!$this->termStorage->exists($parentId)) {
            return false;
        }

        $term = $this->termStorage->loadEntity($parentId);
        return isset($term['taxonomies']) && isset($term['taxonomies'][$id]);
    }

    protected function createChangeInfo($oldEntity, $newEntity, $action = null) {
        // change info is returned directly from save / delete methods
    }

    public function prepareStorage() {
        $this->termStorage->prepareStorage();
    }

    public function getEntityFilename($id, $parentId) {
        return $this->termStorage->getEntityFilename($parentId);
    }

    public function getPathCommonToAllEntities() {
        return $this->termStorage->getPathCommonToAllEntities();
    }

    public function saveLater($data) {
        $this->save($data); // todo
    }

    public function commit() {
    }
}
