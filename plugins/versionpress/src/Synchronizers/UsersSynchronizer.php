<?php
namespace VersionPress\Synchronizers;

use VersionPress\Database\DbSchemaInfo;
use VersionPress\Storages\Storage;
use VersionPress\Utils\AbsoluteUrlReplacer;
use wpdb;

/**
 * Users synchronizer, does quite strict filtering of entity content (only allows
 * a couple of properties to be set).
 */
class UsersSynchronizer extends SynchronizerBase {
    function __construct(Storage $storage, $wpdb, DbSchemaInfo $dbSchema, AbsoluteUrlReplacer $urlReplacer) {
        parent::__construct($storage, $wpdb, $dbSchema, $urlReplacer, 'user');
    }
}
