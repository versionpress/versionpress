<?php

class CommentStorage extends DirectoryStorage implements EntityStorage {

    function __construct($directory) {
        parent::__construct($directory, 'comment', 'comment_ID');
    }

    protected function createChangeInfo($entity, $changeType) {
        global $wpdb;
        $result = $wpdb->get_row("SELECT post_title FROM {$wpdb->prefix}comments JOIN {$wpdb->prefix}posts ON comment_post_ID = ID WHERE comment_ID = " . $entity[$this->idColumnName]);
        $author = $entity["comment_author"];
        $postTitle = $result->post_title;
        return new CommentChangeInfo($changeType, $entity["vp_id"], $author, $postTitle);
    }
}