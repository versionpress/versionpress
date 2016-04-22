<?php

namespace VersionPress\Synchronizers;

use VersionPress\Utils\WordPressCacheUtils;
use wpdb;

class PostMetaSynchronizer extends SynchronizerBase
{

    protected function doEntitySpecificActions()
    {
        parent::doEntitySpecificActions();
        WordPressCacheUtils::clearPostCache(array_column($this->entities, 'vp_post_id'), $this->database);
    }
}
