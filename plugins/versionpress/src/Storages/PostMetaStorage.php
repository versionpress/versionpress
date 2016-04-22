<?php

namespace VersionPress\Storages;

use VersionPress\ChangeInfos\PostMetaChangeInfo;
use VersionPress\Database\EntityInfo;

class PostMetaStorage extends MetaEntityStorage
{
    public function __construct(PostStorage $storage, EntityInfo $entityInfo)
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
        $postTitle = $newParentEntity['post_title'];
        $postType = $newParentEntity['post_type'];
        $postVpId = $newParentEntity['vp_id'];

        $vpId = $newEntity['vp_id'];
        $metaKey = $newEntity['meta_key'];

        return new PostMetaChangeInfo($action, $vpId, $postType, $postTitle, $postVpId, $metaKey);
    }
}
