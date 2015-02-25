<?php

namespace VersionPress\Tests\End2End;

class CommentTestSeleniumPerformer extends SeleniumPerformer implements ICommentTestPerformer {

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
}