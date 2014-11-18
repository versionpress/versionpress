<?php

class CommentStorage extends DirectoryStorage {

    function __construct($directory) {
        parent::__construct($directory, 'comment', 'comment_ID');
    }

    protected function createChangeInfo($oldEntity, $newEntity, $action = null) {

        if (!$action) {

            $diff = EntityUtils::getDiff($oldEntity, $newEntity);

            if (isset($diff['comment_approved']) && $diff['comment_approved'] === 'trash') {
                $action = 'trash';
            } elseif (isset($diff['comment_approved']) && $oldEntity['comment_approved'] === 'trash') {
                $action = 'untrash';
            } else {
                $action = 'edit';
            }
        }

        $author = $newEntity["comment_author"];

        global $wpdb;
        $result = $wpdb->get_row("SELECT post_title FROM {$wpdb->prefix}posts JOIN {$wpdb->prefix}vp_id ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}vp_id.id WHERE vp_id = UNHEX('$newEntity[vp_comment_post_ID]')");
        $postTitle = $result->post_title;

        return new CommentChangeInfo($action, $newEntity["vp_id"], $author, $postTitle);
    }

}