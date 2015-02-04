<?php

namespace VersionPress\Storages;

use Nette\Utils\Strings;
use VersionPress\ChangeInfos\PostMetaChangeInfo;

class PostMetaStorage extends DirectoryStorage {

    private $postMetaKey;
    private $postMetaVpId;


    function save($data) {

        if (!$this->shouldBeSaved($data)) {
            return null;
        }

        $transformedData = $this->transformToPostField($data);

        $this->postMetaKey = $data['meta_key'];
        $this->postMetaVpId = $data['vp_id'];

        return parent::save($transformedData);
    }

    function saveAll($entities) {
        foreach ($entities as $entity) {
            $data = $this->transformToPostField($entity);
            parent::save($data);
        }
    }

    function delete($restriction) {
        $filename = $this->getEntityFilename($restriction['vp_post_id']);
        $post = $this->deserializeEntity(file_get_contents($filename));
        $fieldToDelete = "";

        foreach ($post as $fieldname => $value) {
            if (Strings::endsWith($fieldname, "#$restriction[vp_id]")) {
                $fieldToDelete = $fieldname;
                break;
            }
        }

        if (!$fieldToDelete)
            return null;

        unset($post[$fieldToDelete]);
        file_put_contents($filename, $this->serializeEntity($post['vp_id'], $post));

        list($metaKey) = explode('#', $fieldToDelete, 2);
        return new PostMetaChangeInfo("delete", $restriction['vp_id'], $post['post_type'], $post['post_title'], $post['vp_id'], $metaKey);
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

    protected function createChangeInfo($oldEntity, $newEntity, $action) {
        $postTitle = $newEntity['post_title'];
        $postType = $newEntity['post_type'];
        $postVpId = $newEntity['vp_id'];

        if ($action === 'edit') {
            // New postmeta is editation of the post, therefore we need to determine the creation.
            $action = isset($oldEntity[$this->getJoinedKey($this->postMetaKey, $this->postMetaVpId)]) ? 'edit' : 'create';
        }

        return new PostMetaChangeInfo($action, $this->postMetaVpId, $postType, $postTitle, $postVpId, $this->postMetaKey);
    }

    private function transformToPostField($values) {
        $key = $this->getJoinedKey($values['meta_key'], $values['vp_id']);
        $data = array(
            'vp_id' => $values['vp_post_id'],
            $key => $values['meta_value']
        );
        return $data;
    }

    /**
     * Returns $metaKey#$vpId from $metaKey and $vpId inputs.
     * It's used in a post file as key representing postmeta.
     *
     * @param $metaKey
     * @param $vpId
     * @return string
     */
    private function getJoinedKey($metaKey, $vpId) {
        return sprintf('%s#%s', $metaKey, $vpId);
    }
}
