<?php

class CommentStorage extends DirectoryStorage {

    protected function createChangeInfo($oldEntity, $newEntity, $action = null) {

        if ($action === 'edit') { // determine more specific edit action

            $diff = EntityUtils::getDiff($oldEntity, $newEntity);
            if (isset($diff['comment_approved']) && (
                ($oldEntity['comment_approved'] === 'trash' && $newEntity['comment_approved'] === 'post-trashed') ||
                ($oldEntity['comment_approved'] === 'post-trashed' && $newEntity['comment_approved'] === 'trash')
                )) {
                $action = 'edit'; // trash -> post-trashed and post-trashed -> trash are not interesting action for us
            } elseif (isset($diff['comment_approved']) && $diff['comment_approved'] === 'trash') {
                $action = 'trash';
            } elseif (isset($diff['comment_approved']) && $oldEntity['comment_approved'] === 'trash') {
                $action = 'untrash';
            }
        }

        $author = $newEntity["comment_author"];

        global $wpdb;
        $result = $wpdb->get_row("SELECT post_title FROM {$wpdb->prefix}posts JOIN {$wpdb->prefix}vp_id ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}vp_id.id WHERE vp_id = UNHEX('$newEntity[vp_comment_post_ID]')");
        $postTitle = $result->post_title;

        return new CommentChangeInfo($action, $newEntity["vp_id"], $author, $postTitle);
    }

}