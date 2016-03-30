<?php
/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 3/29/16
 * Time: 10:32 AM
 */

namespace VersionPress\Synchronizers;


use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\ShortcodesReplacer;
use VersionPress\Storages\Storage;
use VersionPress\Utils\AbsoluteUrlReplacer;
use VersionPress\Utils\WordPressCacheUtils;

class CommentMetaSynchronizer extends SynchronizerBase{

    function __construct(Storage $storage, Database $database, DbSchemaInfo $dbSchema, AbsoluteUrlReplacer $urlReplacer, ShortcodesReplacer $shortcodesReplacer) {
        parent::__construct($storage, $database, $dbSchema, $urlReplacer, $shortcodesReplacer, 'commentmeta');
    }

    protected function doEntitySpecificActions() {
        parent::doEntitySpecificActions();
        WordPressCacheUtils::clearCommentCache(array_column($this->entities, 'vp_id'), $this->database);
    }

}
