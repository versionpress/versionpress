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
}