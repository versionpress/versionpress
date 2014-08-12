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
        chdir(self::$config->getSitePath());
        $gitLog = Git::log();
        $lastCommit = $gitLog[0];
        $postTitleInCommit = $lastCommit->getMessage()->getVersionPressTag(PostChangeInfo::POST_TITLE_TAG);
        $this->assertEquals($post["post_title"], $postTitleInCommit);
    }
}