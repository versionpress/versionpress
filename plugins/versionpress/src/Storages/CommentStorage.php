<?php

namespace VersionPress\Storages;

use VersionPress\ChangeInfos\CommentChangeInfo;
use VersionPress\Utils\EntityUtils;

class CommentStorage extends DirectoryStorage {

    protected function createChangeInfo($oldEntity, $newEntity, $action = null) {

        if ($action === 'edit') {
            $diff = EntityUtils::getDiff($oldEntity, $newEntity);
        }

        if (isset($diff['comment_approved'])) { // determine more specific edit action
            if (
                ($oldEntity['comment_approved'] === 'trash' && $newEntity['comment_approved'] === 'post-trashed') ||
                ($oldEntity['comment_approved'] === 'post-trashed' && $newEntity['comment_approved'] === 'trash')
            ) {
                $action = 'edit'; // trash -> post-trashed and post-trashed -> trash are not interesting action for us
            } elseif ($diff['comment_approved'] === 'trash') {
                $action = 'trash';
            } elseif ($oldEntity['comment_approved'] === 'trash') {
                $action = 'untrash';
            } elseif ($oldEntity['comment_approved'] == 0 && $newEntity['comment_approved'] == 1) {
                $action = 'approve';
            } elseif ($oldEntity['comment_approved'] == 1 && $newEntity['comment_approved'] == 0) {
                $action = 'unapprove';
            }
        }

        if ($action === 'create' && $newEntity['comment_approved'] == 0) {
            $action = 'create-pending';
        }

        $author = $newEntity["comment_author"];

        global $wpdb;
        $result = $wpdb->get_row("SELECT post_title FROM {$wpdb->prefix}posts JOIN {$wpdb->prefix}vp_id ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}vp_id.id WHERE vp_id = UNHEX('$newEntity[vp_comment_post_ID]')");
        $postTitle = $result->post_title;

        return new CommentChangeInfo($action, $newEntity["vp_id"], $author, $postTitle);
    }

}