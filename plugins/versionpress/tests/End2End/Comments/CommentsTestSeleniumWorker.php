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

    public function prepare_editTwoComments() {
        throw new \PHPUnit_Framework_SkippedTestError('There is no way to edit more comments at once using selenium');
    }

    public function editTwoComments() {
    }

    public function prepare_deleteTwoComments() {
        $trashedComment = $this->prepareTestComment();
        $trashedComment['comment_approved'] = 'trash';

        self::$wpAutomation->createComment($trashedComment);
        self::$wpAutomation->createComment($trashedComment);
    }

    public function deleteTwoComments() {
        $this->url('wp-admin/edit-comments.php?comment_status=trash');
        $this->performBulkActionWithTwoLastComments('delete');
    }

    public function prepare_moveTwoCommentsInTrash() {
        $trashedComment = $this->prepareTestComment();

        self::$wpAutomation->createComment($trashedComment);
        self::$wpAutomation->createComment($trashedComment);
    }

    public function moveTwoCommentsInTrash() {
        $this->url('wp-admin/edit-comments.php');
        $this->performBulkActionWithTwoLastComments('trash');
    }

    public function prepare_moveTwoCommentsFromTrash() {
    }

    public function moveTwoCommentsFromTrash() {
        $this->url('wp-admin/edit-comments.php?comment_status=trash');
        $this->performBulkActionWithTwoLastComments('untrash');
    }

    public function prepare_markTwoCommentsAsSpam() {
    }

    public function markTwoCommentsAsSpam() {
        $this->url('wp-admin/edit-comments.php');
        $this->performBulkActionWithTwoLastComments('spam');
    }

    public function prepare_markTwoSpamCommentsAsNotSpam() {
    }

    public function markTwoSpamCommentsAsNotSpam() {
        $this->url('wp-admin/edit-comments.php?comment_status=spam');
        $this->performBulkActionWithTwoLastComments('unspam');
    }

    private function performBulkActionWithTwoLastComments($action) {
        // select two last comments
        $this->jsClick('table.comments tbody tr:nth-child(1) .check-column input[type=checkbox]');
        $this->jsClick('table.comments tbody tr:nth-child(2) .check-column input[type=checkbox]');
        // choose bulk edit
        $this->select($this->byId('bulk-action-selector-top'))->selectOptionByValue($action);
        $this->jsClickAndWait('#doaction');
    }

    public function prepare_unapproveTwoComments() {
    }

    public function unapproveTwoComments() {
        $this->url('wp-admin/edit-comments.php');
        $this->performBulkActionWithTwoLastComments('unapprove');
    }

    public function prepare_approveTwoComments() {
    }

    public function approveTwoComments() {
        $this->url('wp-admin/edit-comments.php?comment_status=moderated');
        $this->performBulkActionWithTwoLastComments('approve');
    }

    private function prepareTestComment() {
        $author = self::$testConfig->testSite->adminName;
        $email = self::$testConfig->testSite->adminEmail;

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
}