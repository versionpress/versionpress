<?php

namespace VersionPress\Storages;

use VersionPress\ChangeInfos\PostChangeInfo;
use VersionPress\Utils\AbsoluteUrlReplacer;
use VersionPress\Utils\EntityUtils;

class PostStorage extends DirectoryStorage {

    public function shouldBeSaved($data) {
        $isExistingEntity = $this->entityExistedBeforeThisRequest($data);

        if ($isExistingEntity && isset($_POST['wp-preview']) && $_POST['wp-preview'] === 'dopreview') // ignore saving draft on preview
            return false;

        if ($isExistingEntity && isset($data['post_status']) && ($data['post_status'] === 'draft' && defined('DOING_AJAX') && DOING_AJAX === true)) // ignoring ajax autosaves
            return false;

        if (!$isExistingEntity && isset($data['post_type']) && $data['post_type'] === 'attachment' && !isset($data['post_title']))
            return false;

        return parent::shouldBeSaved($data);
    }

    protected function createChangeInfo($oldEntity, $newEntity, $action) {
        $diff = array();
        if ($action === 'edit') { // determine more specific edit action

            $diff = EntityUtils::getDiff($oldEntity, $newEntity);

            if (isset($diff['post_status']) && $diff['post_status'] === 'trash') {
                $action = 'trash';
            } elseif (isset($diff['post_status']) && $oldEntity['post_status'] === 'trash') {
                $action = 'untrash';
            } elseif (isset($diff['post_status']) && $oldEntity['post_status'] === 'draft' && $newEntity['post_status'] === 'publish') {
                $action = 'publish';
            }
        }

        if ($action == 'create' && $newEntity['post_status'] === 'draft') {
            $action = 'draft';
        }

        $title = $newEntity['post_title'];
        $type = $newEntity['post_type'];

        return new PostChangeInfo($action, $newEntity['vp_id'], $type, $title, array_keys($diff));
    }
}
