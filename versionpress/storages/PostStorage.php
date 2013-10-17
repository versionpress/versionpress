<?php

class PostStorage extends DirectoryStorage implements EntityStorage {

    function __construct($directory) {
        parent::__construct($directory, 'post');
    }

    /**
     * Don't save revisions and drafts
     */
    public function shouldBeSaved($data) {
        $id = $data[$this->idColumnName];
        $isExistingEntity = $this->isExistingEntity($id);

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
}