<?php

class CommentStorage extends DirectoryStorage implements EntityStorage {

    function __construct($directory) {
        parent::__construct($directory, 'comment', 'comment_ID');
    }

    protected function createChangeInfo($entity, $changeType) {
        global $wpdb;
        $result = $wpdb->get_row("SELECT post_title FROM {$wpdb->prefix}posts JOIN {$wpdb->prefix}vp_id ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}vp_id.id WHERE vp_id = UNHEX('$entity[vp_comment_post_ID]')");
        $author = $entity["comment_author"];
        $postTitle = $result->post_title;
        return new CommentChangeInfo($changeType, $entity["vp_id"], $author, $postTitle);
    }

    protected function getEditAction($diff, $oldEntity, $newEntity) {
        if(isset($diff['comment_approved']) && $diff['comment_approved'] === 'trash')
            return 'trash';
        if(isset($diff['comment_approved']) && $oldEntity['comment_approved'] === 'trash')
            return 'untrash';
        return parent::getEditAction($diff, $oldEntity, $newEntity);
    }
}