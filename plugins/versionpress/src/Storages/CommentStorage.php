<?php

namespace VersionPress\Storages;

use VersionPress\ChangeInfos\CommentChangeInfo;
use VersionPress\Database\Database;
use VersionPress\Utils\EntityUtils;

class CommentStorage extends DirectoryStorage
{
    /**
     * @var Database
     */
    private $database;

    public function __construct($directory, $entityInfo, $database)
    {
        parent::__construct($directory, $entityInfo);
        $this->database = $database;
    }

    public function shouldBeSaved($data)
    {
        $isExistingEntity = $this->entityExistedBeforeThisRequest($data);

        if ($isExistingEntity && $data['comment_approved'] === 'spam') {
            return true;
        }

        return parent::shouldBeSaved($data);
    }

    protected function createChangeInfo($oldEntity, $newEntity, $action = null)
    {

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


        $result = $this->database->get_row("SELECT post_title FROM {$this->database->posts} " .
            "JOIN {$this->database->vp_id} ON {$this->database->posts}.ID = {$this->database->vp_id}.id " .
            "WHERE vp_id = UNHEX('$newEntity[vp_comment_post_ID]')");

        $postTitle = $result->post_title;

        return new CommentChangeInfo($action, $newEntity["vp_id"], $author, $postTitle);
    }
}
