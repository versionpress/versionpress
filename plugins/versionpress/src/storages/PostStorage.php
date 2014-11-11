<?php

class PostStorage extends DirectoryStorage implements EntityStorage {

    function __construct($directory) {
        parent::__construct($directory, 'post');
        $this->addFilter(new AbsoluteUrlFilter());
    }

    /**
     * Don't save revisions and drafts
     */
    public function shouldBeSaved($data) {
        $id = @$data['vp_id'];
        $isExistingEntity = !empty($id) && $this->isExistingEntity($id);

        if (isset($data['post_type']) && ($data['post_type'] === 'revision'))
            return false;

        if (isset($data['post_status']) && ($data['post_status'] === 'auto-draft'))
            return false;

        if (isset($data['post_status']) && ($data['post_status'] === 'draft' && DOING_AJAX === true)) // ignoring ajax autosaves
            return false;

        if (!$isExistingEntity && !isset($data['post_type']))
            return false;

        if(!$isExistingEntity && $data['post_type'] === 'attachment' && !isset($data['post_title']))
            return false;

        return true;
    }

    protected function removeUnwantedColumns($entity) {
        static $excludeList = array('comment_count', 'post_modified', 'post_modified_gmt');
        foreach ($excludeList as $excludeKey) {
            unset($entity[$excludeKey]);
        }

        return $entity;
    }

    protected function getEditAction($diff, $oldEntity, $newEntity) {
        if(isset($diff['post_status']) && $diff['post_status'] === 'trash')
            return 'trash';
        if(isset($diff['post_status']) && $oldEntity['post_status'] === 'trash')
            return 'untrash';
        if(isset($diff['post_status']) && $oldEntity['post_status'] === 'draft' && $newEntity['post_status'] === 'publish')
            return 'publish';
        return parent::getEditAction($diff, $oldEntity, $newEntity);
    }

    protected function createChangeInfo($oldEntity, $newEntity, $changeType) {
        $title = $newEntity['post_title'];
        $type = $newEntity['post_type'];

        if(!isset($oldEntity['post_status']) && isset($newEntity['post_status']) && $newEntity['post_status'] === 'draft') {
            $changeType = 'draft';
        }

        return new PostChangeInfo($changeType, $newEntity['vp_id'], $type, $title);
    }
}