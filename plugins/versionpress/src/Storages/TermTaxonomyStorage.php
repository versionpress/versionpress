<?php
namespace VersionPress\Storages;

use VersionPress\ChangeInfos\TermChangeInfo;

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
class TermTaxonomyStorage extends SingleFileStorage {

    protected $notSavedFields = array('vp_term_id', 'count', 'term_id', 'term_taxonomy_id');

    private $currentlySavedTaxonomy;

    public function save($data) {
        $this->currentlySavedTaxonomy = $data['taxonomy'];

        $this->loadEntities();
        $termId = $this->findTermId($data);

        if (!$termId) {
            return null;
        }

        $taxonomyVpid = $data['vp_id'];

        if (!isset($this->entities[$termId]['taxonomies'])) {
            $this->entities[$termId]['taxonomies'] = array();
        }

        $originalTaxonomies = $this->entities[$termId]['taxonomies'];

        $isNew = !isset($originalTaxonomies[$taxonomyVpid]);

        $this->updateTaxonomy($termId, $taxonomyVpid, $data);

        if ($this->entities[$termId]['taxonomies'] != $originalTaxonomies) {
            $this->saveEntities();
            return $this->createChangeInfo(null, $this->entities[$termId], $isNew ? 'create' : 'edit');
        } else {
            return null;
        }
    }

    public function delete($restriction) {
        $taxonomyId = $restriction['vp_id'];

        $this->loadEntities();
        $termId = $this->findTermId($restriction);

        if ($termId === null)
            return null;
        $originalTerm = $this->entities[$termId];
        $originalTaxonomies = $originalTerm['taxonomies'];
        unset($this->entities[$termId]['taxonomies'][$taxonomyId]);
        if ($this->entities[$termId]['taxonomies'] != $originalTaxonomies) {
            $this->saveEntities();
            return $this->createChangeInfo(null, $originalTerm, 'delete');
        } else {
            return null;
        }

    }

    function loadEntity($id) {
        $this->loadEntities();
        foreach ($this->entities as $term) {
            if (isset($term['taxonomies']) && isset($term['taxonomies'][$id])){
                $taxonomy = $term['taxonomies'][$id];
                $taxonomy['vp_term_id'] = $term['vp_id'];
                return $taxonomy;
            }
        }
        return null;
    }

    function loadAll() {
        $this->loadEntities();
        $taxonomies = array();

        foreach ($this->entities as $term) {
            if (isset($term['taxonomies'])) {
                foreach ($term['taxonomies'] as $taxonomy) {
                    $taxonomy['vp_term_id'] = $term['vp_id'];
                    $taxonomies[] = $taxonomy;
                }
            }
        }

        return $taxonomies;
    }


    public function shouldBeSaved($data) {
        return !(count($data) === 2 && isset($data['count'], $data[$this->entityInfo->idColumnName]));
    }

    public function exists($id) {
        $this->loadAll();
        foreach ($this->entities as $term) {
            if (isset($term['taxonomies']) && isset($term['taxonomies'][$id])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Finds term ID related to the taxonomy, or null if no such term exists
     *
     * @param $data
     * @return string|null
     */
    private function findTermId($data) {
        $taxonomyVpid = $data['vp_id'];

        foreach ($this->entities as $termId => $term) {
            if (isset($term['taxonomies'][$taxonomyVpid])
                || (isset($data['vp_term_id']) && strval($term['vp_id']) == strval($data['vp_term_id']))
            ) {
                return $termId;
            }
        }

        return null;
    }

    private function updateTaxonomy($termId, $taxonomyId, $data) {
        $taxonomies = &$this->entities[$termId]['taxonomies'];

        if (!isset($taxonomies[$taxonomyId]))
            $taxonomies[$taxonomyId] = array();

        foreach ($this->notSavedFields as $field)
            unset($data[$field]);

        foreach ($data as $field => $value)
            $taxonomies[$taxonomyId][$field] = $value;
    }

    protected function createChangeInfo($oldEntity, $newEntity, $action = null) {
        // Whatever operation on term-taxonomy, it is always an 'edit' action on the related term
        return new TermChangeInfo('edit', $newEntity['vp_id'], $newEntity['name'], $this->currentlySavedTaxonomy);
    }
}
