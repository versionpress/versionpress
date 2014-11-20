<?php

class PostMetaStorage extends DirectoryStorage {

    private $postMetaKey;
    private $postMetaVpId;

    function __construct($directory) {
        parent::__construct($directory, 'post');
    }

    function save($data) {

        if (!$this->shouldBeSaved($data)) {
            return null;
        }

        $transformedData = $this->transformToPostField($data);

        $this->postMetaKey = $data['meta_key'];
        $this->postMetaVpId = $data['vp_id'];

        parent::save($transformedData);
    }

    function saveAll($entities) {
        foreach ($entities as $entity) {
            $data = $this->transformToPostField($entity);
            parent::save($data);
        }
    }

    public function shouldBeSaved($data) {

        // This method is called either with the original data where 'meta_key' exists,
        // or from the parent::save() in which case the data is already transformed. We need
        // to support both cases.

        $postMetaKey = isset($data['meta_key']) ? $data['meta_key'] : $this->postMetaKey;

        $ignoredMeta = array(
            '_edit_lock',
            '_edit_last',
            '_pingme',
            '_encloseme'
        );
        return !in_array($postMetaKey, $ignoredMeta);
    }


    protected function createChangeInfo($oldEntity, $newEntity, $action = null) {
        $postTitle = $newEntity['post_title'];
        $postType = $newEntity['post_type'];
        $postVpId = $newEntity['vp_id'];

        return new PostMetaChangeInfo($action, $this->postMetaVpId, $postType, $postTitle, $postVpId, $this->postMetaKey);
    }

    private function transformToPostField($values) {
        $key = sprintf('%s#%s', $values['meta_key'], $values['vp_id']);
        $data = array(
            'vp_id' => $values['vp_post_id'],
            $key => $values['meta_value']
        );
        return $data;
    }
}