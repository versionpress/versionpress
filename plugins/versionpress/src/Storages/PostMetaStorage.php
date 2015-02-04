<?php

namespace VersionPress\Storages;

use Nette\Utils\Strings;
use VersionPress\ChangeInfos\PostMetaChangeInfo;
use VersionPress\Utils\ArrayUtils;

class PostMetaStorage extends MetaEntityStorage {
    function __construct(PostStorage $storage) {
        parent::__construct($storage, 'meta_key', 'meta_value', 'vp_post_id');
    }

    protected function createChangeInfoWithParentEntity($oldEntity, $newEntity, $oldParentEntity, $newParentEntity, $action) {
        $postTitle = $newParentEntity['post_title'];
        $postType = $newParentEntity['post_type'];
        $postVpId = $newParentEntity['vp_id'];

        $vpId = $newEntity['vp_id'];
        $metaKey = $newEntity['meta_key'];

        return new PostMetaChangeInfo($action, $vpId, $postType, $postTitle, $postVpId, $metaKey);
    }

    public function shouldBeSaved($data) {
        $ignoredMeta = array(
            '_edit_lock',
            '_edit_last',
            '_pingme',
            '_encloseme'
        );

        return !in_array($data['meta_key'], $ignoredMeta);
    }
}
