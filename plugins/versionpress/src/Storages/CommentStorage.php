<?php

namespace VersionPress\Storages;

use VersionPress\ChangeInfos\CommentChangeInfo;
use VersionPress\Database\Database;
use VersionPress\Utils\EntityUtils;

class CommentStorage extends DirectoryStorage
{
    public function shouldBeSaved($data)
    {
        $isExistingEntity = $this->entityExistedBeforeThisRequest($data);

        if ($isExistingEntity && isset($data['comment_approved']) && $data['comment_approved'] === 'spam') {
            return true;
        }

        return parent::shouldBeSaved($data);
    }
}
