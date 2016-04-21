<?php

namespace VersionPress\Synchronizers;

use VersionPress\Utils\WordPressCacheUtils;

class UserMetaSynchronizer extends SynchronizerBase
{

    protected function doEntitySpecificActions()
    {
        parent::doEntitySpecificActions();
        WordPressCacheUtils::clearUserCache(array_column($this->entities, 'vp_user_id'), $this->database);
    }
}
