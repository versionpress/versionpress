<?php

namespace VersionPress\Storages;

use Nette\Utils\Strings;
use VersionPress\ChangeInfos\PostMetaChangeInfo;

class PostMetaStorage extends MetaEntityStorage {
    function __construct(PostStorage $storage) {
        parent::__construct($storage, 'meta_key', 'meta_value', 'vp_post_id');
    }

    protected function createChangeInfoWithParentEntity($oldEntity, $newEntity, $oldParentEntity, $newParentEntity, $action) {
        $postTitle = $this->getFieldFromOneOrSecond('post_title', $newParentEntity, $oldParentEntity);
        $postType = $this->getFieldFromOneOrSecond('post_type', $newParentEntity, $oldParentEntity);
        $postVpId = $this->getFieldFromOneOrSecond('vp_id', $newParentEntity, $oldParentEntity);

        $vpId = $this->getFieldFromOneOrSecond('vp_id', $newEntity, $oldEntity);
        $metaKey = $this->getFieldFromOneOrSecond('meta_key', $newEntity, $oldEntity);

        return new PostMetaChangeInfo($action, $vpId, $postType, $postTitle, $postVpId, $metaKey);
    }

    private function getFieldFromOneOrSecond($field, $entity1, $entity2) {
        if (isset($entity1) && isset($entity1[$field])) {
            return $entity1[$field];
        }

        return $entity2[$field];
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
