<?php

namespace VersionPress\Synchronizers;

use VersionPress\Utils\WordPressCacheUtils;

class CommentMetaSynchronizer extends SynchronizerBase
{

    protected function doEntitySpecificActions()
    {
        parent::doEntitySpecificActions();
        WordPressCacheUtils::clearCommentCache(array_column($this->entities, 'vp_id'), $this->database);
    }
}
