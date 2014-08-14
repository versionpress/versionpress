<?php

class PostTest extends WpCliTestCase {

    private $somePost = array(
        "post_type" => "post",
        "post_status" => "publish",
        "post_title" => "Hello VersionPress!",
        "post_date" => "2011-11-11 11:11:11",
        "post_content" => "Welcome to versioned WordPress!",
        "post_author" => 1
    );

    public static function setUpBeforeClass() {
        WpAutomation::setUpSite();
        WpAutomation::installVersionPress();
        WpAutomation::enableVersionPress();
    }

    public function testNewPost() {
        WpAutomation::createPost($this->somePost);

        $lastCommit = $this->getLastCommit();
        $comitAction = $lastCommit->getMessage()->getVersionPressTag(ChangeInfo::ACTION_TAG);
        $postTitleInCommit = $lastCommit->getMessage()->getVersionPressTag(PostChangeInfo::POST_TITLE_TAG);
        $this->assertStringStartsWith("post/create", $comitAction);
        $this->assertEquals($this->somePost["post_title"], $postTitleInCommit);

        list($_, $__, $postVpId) = explode("/", $comitAction, 3);
        $commitedPost = $this->getCommitedPost($postVpId);
        $this->assertPostEquals($this->somePost, $commitedPost);
        $this->assertIdExistsInDatabase($postVpId);
    }

    public function testEditPost() {
        $newPost = $this->somePost;
        $changes = array(
            "post_title" => "Announcing VersionPress!"
        );
        $this->assertEditation($newPost, $changes, "post/edit");
    }

    public function testMovePostToTrash() {
        $newPost = $this->somePost;
        $changes = array(
            "post_status" => "trash"
        );
        $this->assertEditation($newPost, $changes, "post/trash");
    }

    public function testMovePostFromTrash() {
        $newPost = $this->somePost;
        $newPost["post_status"] = "trash";
        $changes = array(
            "post_status" => "publish"
        );
        $this->assertEditation($newPost, $changes, "post/untrash");
    }

    public function testDeletePost() {
        $newPost = $this->somePost;

        $id = WpAutomation::createPost($newPost);
        $creationCommit = $this->getLastCommit();
        $createdPostVpId = $this->getPostVpId($creationCommit);

        WpAutomation::deletePost($id);
        $deleteCommit = $this->getLastCommit();
        $this->assertStringStartsWith("post/delete", $deleteCommit->getMessage()->getVersionPressTag(ChangeInfo::ACTION_TAG));

        $deletedPostVpId = $this->getPostVpId($deleteCommit);
        $this->assertEquals($createdPostVpId, $deletedPostVpId);
    }

    /**
     * Returns last commit within tested WP site
     *
     * @return Commit
     */
    private function getLastCommit() {
        chdir(self::$config->getSitePath());
        $gitLog = Git::log();
        $lastCommit = $gitLog[0];
        return $lastCommit;
    }

    private function getCommitedPost($postId) {
        $path = self::$config->getSitePath() . '/wp-content/plugins/versionpress/db/posts/' . $postId . '.ini';
        return IniSerializer::deserialize(file_get_contents($path));
    }

    private function assertPostEquals($expectedPost, $actualPost) {
        $postIsOk = true;
        $errorMessages = array();

        foreach ($expectedPost as $field => $value) {
            if (isset($actualPost[$field])) {
                $fieldIsOk = $value == $actualPost[$field];
                $postIsOk &= $fieldIsOk;
                if (!$fieldIsOk) {
                    $errorMessages[] = "Field '$field' has wrong value";
                }
            } elseif (isset($actualPost["vp_$field"])) {
                // OK ... there is some VP id
            } else {
                $postIsOk = false;
                $errorMessages[] = "Field '$field' not found in post";
            }
        }

        if ($postIsOk) {
            $this->assertTrue(true); // OK
        }
        else {
            $this->fail(join("\n", $errorMessages));
        }
    }

    private function assertIdExistsInDatabase($postId) {
        $dbHost = self::$config->getDbHost();
        $dbName = self::$config->getDbName();
        $dbUser = self::$config->getDbUser();
        $dbPassword = self::$config->getDbPassword();
        $db = new NConnection("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
        $result = boolval($db->query("SELECT * FROM wp_vp_id WHERE vp_id=UNHEX('$postId')"));
        $this->assertTrue($result, "vp_id '$postId' not found in database");
    }

    private function getPostVpId(Commit $commit) {
        list($_, $__, $postVpId) = explode(
            "/",
            $commit->getMessage()->getVersionPressTag(ChangeInfo::ACTION_TAG)
        );
        return $postVpId;
    }

    /**
     * Creates new post, applies changes and checks that actual action corresponds with the expected one.
     * Also checks there was edited the right post.
     *
     * @param $newPost
     * @param $changes
     */
    protected function assertEditation($newPost, $changes, $expectedAction) {
        $id = WpAutomation::createPost($newPost);
        $creationCommit = $this->getLastCommit();
        $createdPostVpId = $this->getPostVpId($creationCommit);

        WpAutomation::editPost($id, $changes);
        $editationCommit = $this->getLastCommit();
        $this->assertStringStartsWith(
            $expectedAction,
            $editationCommit->getMessage()->getVersionPressTag(ChangeInfo::ACTION_TAG),
            "Expected another action"
        );

        $editedPostVpId = $this->getPostVpId($editationCommit);
        $this->assertEquals($createdPostVpId, $editedPostVpId, "Edited different post");
    }
}