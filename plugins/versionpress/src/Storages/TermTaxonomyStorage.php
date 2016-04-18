<?php
namespace VersionPress\Storages;

use VersionPress\ChangeInfos\TermChangeInfo;
use VersionPress\ChangeInfos\TermTaxonomyChangeInfo;
use VersionPress\Database\EntityInfo;

class TermTaxonomyStorage extends DirectoryStorage {

    /** @var TermStorage */
    private $termStorage;

    public function __construct($directory, EntityInfo $entityInfo, TermStorage $termStorage) {
        parent::__construct($directory, $entityInfo);
        $this->termStorage = $termStorage;
    }

    protected function createChangeInfo($oldEntity, $newEntity, $action) {
        $taxonomy = isset($newEntity['taxonomy']) ? $newEntity['taxonomy'] : $oldEntity['taxonomy'];
        $vpid = isset($newEntity['vp_id']) ? $newEntity['vp_id'] : $oldEntity['vp_id'];
        $termVpid = isset($newEntity['vp_term_id']) ? $newEntity['vp_term_id'] : $oldEntity['vp_term_id'];

        $term = $this->termStorage->loadEntity($termVpid);
        $termName = $term ? $term['name'] : "deleted $taxonomy";

        return new TermTaxonomyChangeInfo($action, $vpid, $taxonomy, $termName);
    }
}
