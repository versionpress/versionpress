<?php

namespace VersionPress\Storages;

use VersionPress\ChangeInfos\TermMetaChangeInfo;
use VersionPress\Database\EntityInfo;

class TermMetaStorage extends MetaEntityStorage
{
    public function __construct(TermStorage $termStorage, EntityInfo $entityInfo)
    {
        parent::__construct($termStorage, $entityInfo, 'meta_key', 'meta_value');
    }

    protected function createChangeInfoWithParentEntity(
        $oldEntity,
        $newEntity,
        $oldParentEntity,
        $newParentEntity,
        $action
    ) {
        $termName = $newParentEntity['name'];
        $termVpid = $newParentEntity['vp_id'];

        $vpid = $newEntity['vp_id'];
        $metaKey = $newEntity['meta_key'];

        return new TermMetaChangeInfo($action, $vpid, $termName, $termVpid, $metaKey);
    }
}
