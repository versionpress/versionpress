<?php

namespace VersionPress\Tests\End2End\Comments;

use VersionPress\Tests\End2End\Utils\WpCliWorker;

class CommentsTestWpCliWorker extends WpCliWorker implements ICommentsTestWorker {

    private $testPostId = 0;
    private $lastCreatedComment;

    public function prepare_createCommentAwaitingModeration() {
        $this->testPostId = $this->createTestPost();
    }

    public function createCommentAwaitingModeration() {
        $comment = array(
            'comment_author' => 'John Tester',
            'comment_author_email' => 'john.tester@example.com',
            'comment_content' => 'Public comment',
            'comment_approved' => "0",
            'comment_post_ID' => $this->testPostId
        );

        $this->wpAutomation->createComment($comment);
    }

    public function prepare_createComment() {
    }

    public function createComment() {
        $author = $this->testConfig->testSite->adminName;
        $email = $this->testConfig->testSite->adminEmail;
        $comment = array(
            'comment_author' => $author,
            'comment_author_email' => $email,
            'comment_content' => 'Comment by ' . $author,
            'user_id' => 1,
            'comment_post_ID' => $this->testPostId
        );

        $this->lastCreatedComment = $this->wpAutomation->createComment($comment);
    }

    public function prepare_editComment() {
    }

    public function editComment() {
        $author = $this->testConfig->testSite->adminName;
        $comment = array(
            'comment_content' => 'Updated comment by ' . $author,
        );
        $this->wpAutomation->editComment($this->lastCreatedComment, $comment);
    }

    public function prepare_trashComment() {
    }

    public function trashComment() {
        $this->wpAutomation->trashComment($this->lastCreatedComment);
    }

    public function prepare_untrashComment() {
    }

    public function untrashComment() {
        $this->wpAutomation->untrashComment($this->lastCreatedComment);
    }

    public function prepare_deleteComment() {
    }

    public function deleteComment() {
        $this->wpAutomation->deleteComment($this->lastCreatedComment);
    }

    public function prepare_unapproveComment() {
        $author = $this->testConfig->testSite->adminName;
        $email = $this->testConfig->testSite->adminEmail;
        $comment = array(
            'comment_author' => $author,
            'comment_author_email' => $email,
            'comment_content' => 'Comment by ' . $author,
            'user_id' => 1,
            'comment_post_ID' => $this->testPostId
        );

        $this->lastCreatedComment = $this->wpAutomation->createComment($comment);
    }

    public function unapproveComment() {
        $this->wpAutomation->unapproveComment($this->lastCreatedComment);
    }

    public function prepare_approveComment() {
    }

    public function approveComment() {
        $this->wpAutomation->approveComment($this->lastCreatedComment);
    }

    public function prepare_markAsSpam() {
    }

    public function markAsSpam() {
        $this->wpAutomation->spamComment($this->lastCreatedComment);
    }

    public function prepare_markAsNotSpam() {
    }

    public function markAsNotSpam() {
        $this->wpAutomation->unspamComment($this->lastCreatedComment);
    }

    private function createTestPost() {
        $post = array(
            "post_type" => "post",
            "post_status" => "publish",
            "post_title" => "Test post for comments",
            "post_date" => "2011-11-11 11:11:11",
            "post_content" => "Test post",
            "post_author" => 1
        );

        return $this->wpAutomation->createPost($post);
    }

    public function prepare_editTwoComments() {
        $this->lastCreatedComment = array();
        $comment = $this->prepareTestComment();

        $this->lastCreatedComment[] = $this->wpAutomation->createComment($comment);
        $this->lastCreatedComment[] = $this->wpAutomation->createComment($comment);
    }

    public function editTwoComments() {
        $this->wpAutomation->runWpCliCommand('comment', 'update', array_merge($this->lastCreatedComment, array('comment_content' => 'Changed content')));
    }

    private function prepareTestComment() {
        $author = $this->testConfig->testSite->adminName;
        $email = $this->testConfig->testSite->adminEmail;

        if (!$this->testPostId) {
            $this->testPostId = $this->createTestPost();
        }

        return array(
            'comment_author' => $author,
            'comment_author_email' => $email,
            'comment_content' => 'Comment by ' . $author,
            'user_id' => 1,
            'comment_post_ID' => $this->testPostId
        );
    }

    public function prepare_commentmetaCreate() {
        $this->lastCreatedComment = array();
        $comment = $this->prepareTestComment();
        $this->lastCreatedComment = $this->wpAutomation->createComment($comment);
    }

    public function commentmetaCreate() {
        $this->wpAutomation->createCommentMeta($this->lastCreatedComment, 'dummy_meta', 'dummy_meta_value');
    }

    public function prepare_deleteTwoComments() {
        $this->lastCreatedComment = array();
        $comment = $this->prepareTestComment();

        $this->lastCreatedComment[] = $this->wpAutomation->createComment($comment);
        $this->lastCreatedComment[] = $this->wpAutomation->createComment($comment);
    }

    public function deleteTwoComments() {
        $this->wpAutomation->runWpCliCommand('comment', 'delete', array_merge($this->lastCreatedComment, array('force' => null)));
    }

    public function prepare_moveTwoCommentsInTrash() {
        $this->lastCreatedComment = array();
        $comment = $this->prepareTestComment();

        $this->lastCreatedComment[] = $this->wpAutomation->createComment($comment);
        $this->lastCreatedComment[] = $this->wpAutomation->createComment($comment);
    }

    public function moveTwoCommentsInTrash() {
        $this->wpAutomation->runWpCliCommand('comment', 'delete', $this->lastCreatedComment);
    }

    public function prepare_moveTwoCommentsFromTrash() {
        $this->lastCreatedComment = array();
        $trashedComment = $this->prepareTestComment();
        $trashedComment['comment_approved'] = 'trash';

        $this->lastCreatedComment[] = $this->wpAutomation->createComment($trashedComment);
        $this->lastCreatedComment[] = $this->wpAutomation->createComment($trashedComment);
    }

    public function moveTwoCommentsFromTrash() {
        $this->wpAutomation->runWpCliCommand('comment', 'update', array_merge($this->lastCreatedComment, array('comment_approved' => 1)));
    }

    public function prepare_markTwoCommentsAsSpam() {
        $this->lastCreatedComment = array();
        $comment = $this->prepareTestComment();

        $this->lastCreatedComment[] = $this->wpAutomation->createComment($comment);
        $this->lastCreatedComment[] = $this->wpAutomation->createComment($comment);
    }

    public function markTwoCommentsAsSpam() {
        $this->wpAutomation->runWpCliCommand('comment', 'update', array_merge($this->lastCreatedComment, array('comment_approved' => 'spam')));
    }

    public function prepare_markTwoSpamCommentsAsNotSpam() {
        $this->lastCreatedComment = array();
        $trashedComment = $this->prepareTestComment();
        $trashedComment['comment_approved'] = 'spam';

        $this->lastCreatedComment[] = $this->wpAutomation->createComment($trashedComment);
        $this->lastCreatedComment[] = $this->wpAutomation->createComment($trashedComment);
    }

    public function markTwoSpamCommentsAsNotSpam() {
        $this->wpAutomation->runWpCliCommand('comment', 'update', array_merge($this->lastCreatedComment, array('comment_approved' => 1)));
    }

    public function prepare_unapproveTwoComments() {
        $this->lastCreatedComment = array();
        $comment = $this->prepareTestComment();

        $this->lastCreatedComment[] = $this->wpAutomation->createComment($comment);
        $this->lastCreatedComment[] = $this->wpAutomation->createComment($comment);
    }

    public function unapproveTwoComments() {
        $this->wpAutomation->runWpCliCommand('comment', 'update', array_merge($this->lastCreatedComment, array('comment_approved' => 0)));
    }

    public function prepare_approveTwoComments() {
        $this->lastCreatedComment = array();
        $comment = $this->prepareTestComment();
        $comment['comment_approved'] = 0;

        $this->lastCreatedComment[] = $this->wpAutomation->createComment($comment);
        $this->lastCreatedComment[] = $this->wpAutomation->createComment($comment);
    }

    public function approveTwoComments() {
        $this->wpAutomation->runWpCliCommand('comment', 'update', array_merge($this->lastCreatedComment, array('comment_approved' => 1)));
    }
}
