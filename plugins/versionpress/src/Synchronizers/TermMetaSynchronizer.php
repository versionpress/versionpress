<?php

namespace VersionPress\Synchronizers;

use VersionPress\Utils\WordPressCacheUtils;

class TermMetaSynchronizer extends SynchronizerBase
{

    protected function doEntitySpecificActions()
    {
        parent::doEntitySpecificActions();
        WordPressCacheUtils::clearTermCache(array_column($this->entities, 'vp_term_id'), $this->database);
    }
}
