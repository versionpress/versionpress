<?php

namespace VersionPress\Synchronizers;

use VersionPress\Database\DbSchemaInfo;
use VersionPress\Storages\Storage;
use wpdb;

class UserMetaSynchronizer extends SynchronizerBase {

    function __construct(Storage $storage, $wpdb, DbSchemaInfo $dbSchema) {
        parent::__construct($storage, $wpdb, $dbSchema, 'usermeta');
    }

}
