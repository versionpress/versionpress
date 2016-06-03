<?php

namespace VersionPress\Storages;

use VersionPress\ChangeInfos\PostChangeInfo;
use VersionPress\Utils\EntityUtils;

class PostStorage extends DirectoryStorage
{

    public function shouldBeSaved($data)
    {
        $isExistingEntity = $this->entityExistedBeforeThisRequest($data);

        // ignore saving draft on preview
        if ($isExistingEntity && isset($_POST['wp-preview']) && $_POST['wp-preview'] === 'dopreview') {
            return false;
        }

        // ignoring ajax autosaves
        if ($isExistingEntity && isset($data['post_status']) && ($data['post_status'] === 'draft' &&
                defined('DOING_AJAX') && DOING_AJAX === true)
        ) {
            return false;
        }

        if (!$isExistingEntity && isset($data['post_type']) && $data['post_type'] === 'attachment' &&
            !isset($data['post_title'])
        ) {
            return false;
        }

        return parent::shouldBeSaved($data);
    }
}
