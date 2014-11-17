<?php

class CommentsSynchronizer extends SynchronizerBase {
    function __construct(Storage $storage, wpdb $database, DbSchemaInfo $dbSchema) {
        parent::__construct($storage, $database, $dbSchema, 'comments');
    }
}