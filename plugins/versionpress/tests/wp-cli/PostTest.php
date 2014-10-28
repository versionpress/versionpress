<?php

class PostTest extends WpCliTestCase {

    private $somePost = array(
        "post_type" => "post",
        "post_status" => "publish",
        "post_title" => "Hello VersionPress",
        "post_date" => "2011-11-11 11:11:11",
        "post_content" => "Welcome to versioned WordPress",
        "post_author" => 1
    );

    public function testNewPost() {
        WpAutomation::createPost($this->somePost);

        $lastCommit = $this->getLastCommit();
        $comitAction = $lastCommit->getMessage()->getVersionPressTag(TrackedChangeInfo::ACTION_TAG);
        $postTitleInCommit = $lastCommit->getMessage()->getVersionPressTag(PostChangeInfo::POST_TITLE_TAG);
        $this->assertStringStartsWith("post/create", $comitAction);
        $this->assertEquals($this->somePost["post_title"], $postTitleInCommit);

        list($_, $__, $postVpId) = explode("/", $comitAction, 3);
        $commitedPost = $this->getCommitedEntity($postVpId);
        $this->assertEntityEquals($this->somePost, $commitedPost);
        $this->assertIdExistsInDatabase($postVpId);
    }

    public function testEditPost() {
        $newPost = $this->somePost;
        $changes = array(
            "post_title" => "Announcing VersionPress!"
        );
        $this->assertPostEditation($newPost, $changes, "post/edit");
    }

    public function testMovePostToTrash() {
        $newPost = $this->somePost;
        $changes = array(
            "post_status" => "trash"
        );
        $this->assertPostEditation($newPost, $changes, "post/trash");
    }

    public function testMovePostFromTrash() {
        $newPost = $this->somePost;
        $newPost["post_status"] = "trash";
        $changes = array(
            "post_status" => "publish"
        );
        $this->assertPostEditation($newPost, $changes, "post/untrash");
    }

    public function testDeletePost() {
        $newPost = $this->somePost;
        $this->assertPostDeletion($newPost);
    }

    protected function getCommitedEntity($postId) {
        $path = self::$config->getSitePath() . '/wp-content/plugins/versionpress/db/posts/' . $postId . '.ini';
        return IniSerializer::deserialize(file_get_contents($path));
    }

    protected function assertPostEditation($newPost, $changes, $expectedAction) {
        $this->assertEditation($newPost, $changes, $expectedAction, "WpAutomation::createPost", "WpAutomation::editPost");
    }

    private function assertPostDeletion($newPost) {
        $this->assertDeletion($newPost, "post", "WpAutomation::createPost", "WpAutomation::deletePost");
    }

}