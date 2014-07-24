<?php

class CommentStorage extends DirectoryStorage implements EntityStorage {

    function __construct($directory) {
        parent::__construct($directory, 'comment', 'comment_ID');
    }

    protected function createChangeInfo($entity, $changeType) {
        global $wpdb, $table_prefix;
        $result = $wpdb->get_row("SELECT post_title FROM {$table_prefix}comments JOIN {$table_prefix}posts ON comment_post_ID = ID WHERE comment_ID = " . $entity[$this->idColumnName]);
        $author = $entity["comment_author"];
        $postTitle = $result->post_title;
        return new CommentChangeInfo($changeType, $entity["vp_id"], $author, $postTitle);
    }
}