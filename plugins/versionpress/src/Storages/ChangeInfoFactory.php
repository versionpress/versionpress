<?php

namespace VersionPress\Storages;

use VersionPress\ChangeInfos\CommentChangeInfo;
use VersionPress\ChangeInfos\CommentMetaChangeInfo;
use VersionPress\ChangeInfos\PostChangeInfo;
use VersionPress\ChangeInfos\PostMetaChangeInfo;
use VersionPress\ChangeInfos\TermChangeInfo;
use VersionPress\ChangeInfos\TermMetaChangeInfo;
use VersionPress\ChangeInfos\TermTaxonomyChangeInfo;
use VersionPress\ChangeInfos\UserChangeInfo;
use VersionPress\ChangeInfos\UserMetaChangeInfo;
use VersionPress\DI\VersionPressServices;
use VersionPress\Utils\EntityUtils;

class ChangeInfoFactory
{
    public static function createTermChangeInfo($oldEntity, $newEntity, $action = null)
    {
        $diff = EntityUtils::getDiff($oldEntity, $newEntity);

        if ($oldEntity && isset($diff['name'])) {
            return new TermChangeInfo('rename', $newEntity['vp_id'], $newEntity['name'], 'term', $oldEntity['name']);
        }

        return new TermChangeInfo($action, $newEntity['vp_id'], $newEntity['name'], 'term');
    }

    public static function createUserChangeInfo($oldEntity, $newEntity, $action = null)
    {
        return new UserChangeInfo($action, $newEntity["vp_id"], $newEntity["user_login"]);
    }

    public static function createTermTaxonomyChangeInfo($oldEntity, $newEntity, $action)
    {
        global $versionPressContainer;

        /** @var StorageFactory $storageFactory */
        $storageFactory = $versionPressContainer->resolve(VersionPressServices::STORAGE_FACTORY);
        $termStorage = $storageFactory->getStorage('term');

        $taxonomy = isset($newEntity['taxonomy']) ? $newEntity['taxonomy'] : $oldEntity['taxonomy'];
        $vpid = isset($newEntity['vp_id']) ? $newEntity['vp_id'] : $oldEntity['vp_id'];
        $termVpid = isset($newEntity['vp_term_id']) ? $newEntity['vp_term_id'] : $oldEntity['vp_term_id'];

        $term = $termStorage->loadEntity($termVpid, null);
        $termName = $term ? $term['name'] : "deleted $taxonomy";

        return new TermTaxonomyChangeInfo($action, $vpid, $taxonomy, $termName);
    }

    public static function createCommentChangeInfo($oldEntity, $newEntity, $action = null)
    {
        global $versionPressContainer;

        /** @var StorageFactory $storageFactory */
        $database = $versionPressContainer->resolve(VersionPressServices::DATABASE);

        if ($action === 'edit') {
            $diff = EntityUtils::getDiff($oldEntity, $newEntity);
        }

        if (isset($diff['comment_approved'])) { // determine more specific edit action
            if (($oldEntity['comment_approved'] === 'trash' && $newEntity['comment_approved'] === 'post-trashed') ||
                ($oldEntity['comment_approved'] === 'post-trashed' && $newEntity['comment_approved'] === 'trash')
            ) {
                $action = 'edit'; // trash -> post-trashed and post-trashed -> trash are not interesting action for us
            } elseif ($diff['comment_approved'] === 'trash') {
                $action = 'trash';
            } elseif ($oldEntity['comment_approved'] === 'trash') {
                $action = 'untrash';
            } elseif ($diff['comment_approved'] === 'spam') {
                $action = 'spam';
            } elseif ($oldEntity['comment_approved'] === 'spam') {
                $action = 'unspam';
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


        $result = $database->get_row("SELECT post_title FROM {$database->posts} " .
            "JOIN {$database->vp_id} ON {$database->posts}.ID = {$database->vp_id}.id " .
            "WHERE vp_id = UNHEX('$newEntity[vp_comment_post_ID]')");

        $postTitle = $result->post_title;

        return new CommentChangeInfo($action, $newEntity["vp_id"], $author, $postTitle);
    }

    public static function createPostChangeInfo($oldEntity, $newEntity, $action)
    {
        $diff = [];
        if ($action === 'edit') { // determine more specific edit action

            $diff = EntityUtils::getDiff($oldEntity, $newEntity);

            if (isset($diff['post_status']) && $diff['post_status'] === 'trash') {
                $action = 'trash';
            } elseif (isset($diff['post_status']) && $oldEntity['post_status'] === 'trash') {
                $action = 'untrash';
            } elseif (isset($diff['post_status']) && $oldEntity['post_status'] === 'draft' &&
                $newEntity['post_status'] === 'publish'
            ) {
                $action = 'publish';
            }
        }

        if ($action == 'create' && $newEntity['post_status'] === 'draft') {
            $action = 'draft';
        }

        $title = $newEntity['post_title'];
        $type = $newEntity['post_type'];

        return new PostChangeInfo($action, $newEntity['vp_id'], $type, $title, array_keys($diff));
    }

    public static function createTermMetaChangeInfo(
        $oldEntity,
        $newEntity,
        $oldParentEntity,
        $newParentEntity,
        $action
    ) {
        $termName = $newParentEntity['name'];
        $termVpid = $newParentEntity['vp_id'];

        $vpid = $newEntity['vp_id'];
        $metaKey = $newEntity['meta_key'];

        return new TermMetaChangeInfo($action, $vpid, $termName, $termVpid, $metaKey);
    }

    public static function createUserMetaChangeInfo(
        $oldEntity,
        $newEntity,
        $oldParentEntity,
        $newParentEntity,
        $action
    ) {
        $userMetaVpId = $newEntity['vp_id'];
        $userLogin = $newParentEntity['user_login'];
        $userMetaKey = $newEntity['meta_key'];
        $userVpId = $newParentEntity['vp_id'];

        return new UserMetaChangeInfo($action, $userMetaVpId, $userLogin, $userMetaKey, $userVpId);
    }

    public static function createCommentMetaChangeInfo(
        $oldEntity,
        $newEntity,
        $oldParentEntity,
        $newParentEntity,
        $action
    ) {
        $commentVpId = $newParentEntity['vp_id'];

        $vpId = $newEntity['vp_id'];
        $metaKey = $newEntity['meta_key'];

        return new CommentMetaChangeInfo($action, $vpId, $commentVpId, $metaKey);
    }

    public static function createPostMetaChangeInfo(
        $oldEntity,
        $newEntity,
        $oldParentEntity,
        $newParentEntity,
        $action
    ) {
        $postTitle = $newParentEntity['post_title'];
        $postType = $newParentEntity['post_type'];
        $postVpId = $newParentEntity['vp_id'];

        $vpId = $newEntity['vp_id'];
        $metaKey = $newEntity['meta_key'];

        return new PostMetaChangeInfo($action, $vpId, $postType, $postTitle, $postVpId, $metaKey);
    }
}
