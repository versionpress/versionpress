<?php

class PostStorage extends DirectoryStorage implements EntityStorage {

    function __construct($directory) {
        parent::__construct($directory, 'post');
    }

    /**
     * Don't save revisions and drafts
     */
    public function shouldBeSaved($data) {
        $id = @$data['vp_id'];
        $isExistingEntity = !empty($id) && $this->isExistingEntity($id);

        if (isset($data['post_type']) && $data['post_type'] === 'revision')
            return false;

        if (isset($data['post_status']) && ($data['post_status'] === 'auto-draft' || $data['post_status'] === 'draft'))
            return false;

        if (!$isExistingEntity && !isset($data['post_type']))
            return false;

        return true;
    }

    protected function removeUnwantedColumns($entity) {
        static $excludeList = array('comment_count');
        foreach ($excludeList as $excludeKey) {
            unset($entity[$excludeKey]);
        }

        return $entity;
    }

    protected function getEditAction($diffData) {
        if(isset($diffData['post_status']) && $diffData['post_status'] === 'trash')
            return 'trash';
        return 'edit';
    }

    protected function createChangeInfo($entity, $changeType) {
        $title = $entity['post_title'];
        return new PostChangeInfo($changeType, $entity['vp_id'], $title);
    }
}