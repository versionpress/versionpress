<?php

class CommentSynchronizer extends PostsAndCommentsBaseSynchronizer {

    function __construct(EntityStorage $storage, wpdb $database, $tableName) {
        parent::__construct($storage, $database, $tableName, 'comment_ID', 'comment_parent');
    }
}