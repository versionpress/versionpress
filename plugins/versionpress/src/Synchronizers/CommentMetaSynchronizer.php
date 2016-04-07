<?php

namespace VersionPress\Synchronizers;


use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\ShortcodesReplacer;
use VersionPress\Storages\Storage;
use VersionPress\Utils\AbsoluteUrlReplacer;
use VersionPress\Utils\WordPressCacheUtils;

class CommentMetaSynchronizer extends SynchronizerBase {

    protected function doEntitySpecificActions() {
        parent::doEntitySpecificActions();
        WordPressCacheUtils::clearCommentCache(array_column($this->entities, 'vp_id'), $this->database);
    }

}
