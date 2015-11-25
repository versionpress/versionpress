<?php

namespace VersionPress\Storages;

use VersionPress\ChangeInfos\TermMetaChangeInfo;
use VersionPress\Database\EntityInfo;

class TermMetaStorage extends MetaEntityStorage {
    function __construct(TermStorage $termStorage) {
        parent::__construct($termStorage, 'meta_key', 'meta_value', 'vp_term_id');
    }

    protected function createChangeInfoWithParentEntity($oldEntity, $newEntity, $oldParentEntity, $newParentEntity, $action) {
        $termName = $newParentEntity['name'];
        $termVpid = $newParentEntity['vp_id'];

        $vpid = $newEntity['vp_id'];
        $metaKey = $newEntity['meta_key'];

        return new TermMetaChangeInfo($action, $vpid, $termName, $termVpid, $metaKey);
    }
}
