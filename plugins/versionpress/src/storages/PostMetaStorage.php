<?php

class PostMetaStorage extends DirectoryStorage {

    private $postMetaKey;
    private $postMetaVpId;

    private $ignoredMeta = array('_edit_lock', '_edit_last', '_pingme', '_encloseme');

    function __construct($directory) {
        parent::__construct($directory, 'post');
    }

    function save($values) {
        if(in_array($values['meta_key'], $this->ignoredMeta)) return;

        $data = $this->transformToPostField($values);

        $this->postMetaKey = $values['meta_key'];
        $this->postMetaVpId = $values['vp_id'];

        $this->saveEntity($data, array($this, 'notifyChangeListeners'));
    }

    function saveAll($entities) {
        foreach ($entities as $entity) {
            $data = $this->transformToPostField($entity);
            $this->saveEntity($data);
        }
    }

    protected function createChangeInfo($entity, $changeType) {
        $postTitle = $entity['post_title'];
        $postType = $entity['post_type'];

        return new PostMetaChangeInfo($changeType, $this->postMetaVpId, $postType, $postTitle, $this->postMetaKey);
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