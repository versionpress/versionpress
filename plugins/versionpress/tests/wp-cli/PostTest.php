<?php

class PostTest extends WpCliTestCase {

    public static function setUpBeforeClass() {
        WpAutomation::setUpSite();
        WpAutomation::installVersionpress();
        WpAutomation::enableVersionPress();
    }

    public function testNewPost() {
        $post = array(
            "post_type" => "post",
            "post_status" => "publish",
            "post_title" => "Hello VersionPress!",
            "post_date" => "2011-11-11 11:11:11",
            "post_content" => "Welcome to versioned WordPress!",
            "post_author" => 1
        );

        WpAutomation::createPost($post);

        $lastCommit = $this->getLastCommit();
        $comitAction = $lastCommit->getMessage()->getVersionPressTag(ChangeInfo::ACTION_TAG);
        $postTitleInCommit = $lastCommit->getMessage()->getVersionPressTag(PostChangeInfo::POST_TITLE_TAG);
        $this->assertStringStartsWith("post/create", $comitAction);
        $this->assertEquals($post["post_title"], $postTitleInCommit);

        list($_, $__, $postId) = explode("/", $comitAction, 3);

        $commitedPost = $this->getCommitedPost($postId);
        $this->assertPostEquals($post, $commitedPost);
        $this->assertIdExistsInDatabase($postId);
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
}