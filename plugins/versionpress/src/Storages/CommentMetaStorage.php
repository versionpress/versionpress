<?php

namespace VersionPress\Storages;

use VersionPress\ChangeInfos\CommentMetaChangeInfo;
use VersionPress\Database\EntityInfo;

class CommentMetaStorage extends MetaEntityStorage
{

    public function __construct(CommentStorage $storage, EntityInfo $entityInfo)
    {
        parent::__construct($storage, $entityInfo, 'meta_key', 'meta_value');
    }

    protected function createChangeInfoWithParentEntity(
        $oldEntity,
        $newEntity,
        $oldParentEntity,
        $newParentEntity,
        $action
    ) {
        $commentVpId = $newParentEntity['vp_id'];

        $vpId = $newEntity['vp_id'];
        $metaKey = $newEntity['meta_key'];

        return new CommentMetaChangeInfo($action, $vpId, $commentVpId, $metaKey);
    }
}
