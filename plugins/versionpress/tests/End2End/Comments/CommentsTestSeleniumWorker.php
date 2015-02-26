<?php

namespace VersionPress\Tests\End2End\Comments;

use VersionPress\Tests\End2End\Utils\SeleniumWorker;

class CommentsTestSeleniumWorker extends SeleniumWorker implements ICommentsTestWorker {

    private $testPostId = 0;

    public function prepare_createCommentAwaitingModeration() {
        $this->logOut();
        $this->testPostId = $this->createTestPost();
    }

    public function createCommentAwaitingModeration() {
        $this->url('?p=' . $this->testPostId);

        $this->byCssSelector('#author')->value("John Tester");
        $this->byCssSelector('#email')->value("john.tester@example.com");
        $this->byCssSelector('#comment')->value("Public comment");

        $this->byCssSelector('#submit')->click();
        $this->waitAfterRedirect();
    }

    public function prepare_createComment() {
        $this->loginIfNecessary();
    }

    public function createComment() {
        $this->createNewComment();
    }

    public function prepare_editComment() {
    }

    public function editComment() {
        $this->clickEditLink();
        $this->waitAfterRedirect();
        $this->setValue('#content', 'Updated comment by admin');
        $this->byCssSelector('#save')->click();
        $this->waitAfterRedirect();
    }

    public function prepare_trashComment() {
    }

    public function trashComment() {
        $this->clickEditLink();
        $this->waitAfterRedirect();
        $this->byCssSelector('#delete-action a')->click();
        $this->waitAfterRedirect();
    }

    protected function logOut() {
        $this->url('wp-login.php?action=logout');
        $this->byCssSelector('body>p>a')->click();
        $this->waitAfterRedirect();
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

        return self::$wpAutomation->createPost($post);
    }

    /**
     * Creates new comment for the test post and stays on that page after postback
     */
    private function createNewComment() {
        $this->url('?p=' . $this->testPostId);
        $this->byCssSelector('#comment')->value("Comment by admin");
        $this->byCssSelector('#submit')->click();
        $this->waitAfterRedirect();
    }

    /**
     * On the post view, clicks the Edit link for the first comment
     */
    private function clickEditLink() {
        $this->byCssSelector('.comment-list li:first-child .comment-edit-link')->click();
    }

    public function prepare_untrashComment() {
    }

    public function untrashComment() {
        $this->url('wp-admin/edit-comments.php?comment_status=trash');
        $this->jsClickAndWait('#the-comment-list tr:first-child .untrash a');
    }

    public function prepare_deleteComment() {
        $this->url('wp-admin/edit-comments.php');
        $this->jsClickAndWait('#the-comment-list tr:first-child .trash a');
        $this->url('wp-admin/edit-comments.php?comment_status=trash');
    }

    public function deleteComment() {
        $this->jsClickAndWait('#the-comment-list tr:first-child .delete a');
        $this->url('wp-admin/edit-comments.php');
    }

    public function prepare_unapproveComment() {
        $this->createNewComment();
    }

    public function unapproveComment() {
        $this->url('wp-admin/edit-comments.php');
        $this->jsClickAndWait('#the-comment-list tr:first-child .unapprove a');
    }

    public function prepare_approveComment() {
    }

    public function approveComment() {
        $this->url('wp-admin/edit-comments.php?comment_status=moderated');
        $this->jsClickAndWait('#the-comment-list tr:first-child .approve a');
    }

    public function prepare_markAsSpam() {
    }

    public function markAsSpam() {
        $this->url('wp-admin/edit-comments.php');
        $this->jsClickAndWait('#the-comment-list tr:first-child .spam a');
    }

    public function prepare_markAsNotSpam() {
    }

    public function markAsNotSpam() {
        $this->url('wp-admin/edit-comments.php?comment_status=spam');
        $this->jsClickAndWait('#the-comment-list tr:first-child .unspam a');
    }
}