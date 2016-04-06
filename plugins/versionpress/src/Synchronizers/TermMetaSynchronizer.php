<?php

namespace VersionPress\Synchronizers;

use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\ShortcodesReplacer;
use VersionPress\Storages\Storage;
use VersionPress\Utils\AbsoluteUrlReplacer;
use VersionPress\Utils\WordPressCacheUtils;

class TermMetaSynchronizer extends SynchronizerBase {

    protected function doEntitySpecificActions() {
        parent::doEntitySpecificActions();
        WordPressCacheUtils::clearTermCache(array_column($this->entities, 'vp_term_id'), $this->database);
    }
}
