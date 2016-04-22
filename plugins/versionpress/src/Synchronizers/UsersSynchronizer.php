<?php
namespace VersionPress\Synchronizers;

use VersionPress\Utils\WordPressCacheUtils;

/**
 * Users synchronizer, does quite strict filtering of entity content (only allows
 * a couple of properties to be set).
 */
class UsersSynchronizer extends SynchronizerBase
{

    protected function doEntitySpecificActions()
    {
        parent::doEntitySpecificActions();
        $this->clearCache();
    }


    private function clearCache()
    {
        WordPressCacheUtils::clearUserCache(array_column($this->entities, 'vp_id'), $this->database);
    }
}
