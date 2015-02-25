<?php

namespace VersionPress\Tests\End2End;

class CommentTestWpCliWorker extends WpCliWorker implements ICommentTestWorker {

    private $testPostId = 0;

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
    }

    public function prepare_editComment() {
    }

    public function editComment() {
    }

    public function prepare_trashComment() {
    }

    public function trashComment() {
    }

    public function prepare_untrashComment() {
    }

    public function untrashComment() {
    }

    public function prepare_deleteComment() {
    }

    public function deleteComment() {
    }

    public function prepare_unapproveComment() {
    }

    public function unapproveComment() {
    }

    public function prepare_approveComment() {
    }

    public function approveComment() {
    }

    public function prepare_markAsSpam() {
    }

    public function markAsSpam() {
    }

    public function prepare_markAsNotSpam() {
    }

    public function markAsNotSpam() {
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