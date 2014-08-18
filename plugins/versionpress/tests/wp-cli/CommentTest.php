<?php

class CommentTest extends WpCliTestCase {
    private $someComment = array(
        "comment_author" => "Mr VersionPress",
        "comment_author_email" => "versionpress@example.com",
        "comment_author_url" => "https://wordpress.org/",
        "comment_date" => "2012-12-12 12:12:12",
        "comment_content" => "Have you heard about VersionPress? It's new awesome version control plugin for WordPress.",
        "comment_approved" => 1,
        "comment_post_ID" => 1,
    );

    public function testNewComment() {
        WpAutomation::createComment($this->someComment);

        $lastCommit = $this->getLastCommit();
        $comitAction = $lastCommit->getMessage()->getVersionPressTag(ChangeInfo::ACTION_TAG);
        $commentAuthorInTag = $lastCommit->getMessage()->getVersionPressTag(CommentChangeInfo::AUTHOR_TAG);
        $this->assertStringStartsWith("comment/create", $comitAction);
        $this->assertEquals($this->someComment["comment_author"], $commentAuthorInTag);

        list($_, $__, $commentVpId) = explode("/", $comitAction, 3);
        $commitedPost = $this->getCommitedEntity($commentVpId);
        $this->assertEntityEquals($this->someComment, $commitedPost);
        $this->assertIdExistsInDatabase($commentVpId);
    }

    public function testEditComment() {
        $newComment = $this->someComment;
        $changes = array(
            "comment_content" => "Announcing VersionPress!"
        );
        $this->assertCommentEditation($newComment, $changes);
    }

    public function testDeleteComment() {
        $newComment = $this->someComment;
        $this->assertCommentDeletion($newComment);
    }

    protected function getCommitedEntity($commentVpId) {
        $path = self::$config->getSitePath() . '/wp-content/plugins/versionpress/db/comments/' . $commentVpId . '.ini';
        return IniSerializer::deserialize(file_get_contents($path));
    }

    private function assertCommentEditation($comment, $changes) {
        $this->assertEditation($comment, $changes, "comment/edit", "WpAutomation::createComment", "WpAutomation::editComment");
    }

    private function assertCommentDeletion($newComment) {
        $this->assertDeletion($newComment, "comment", "WpAutomation::createComment", "WpAutomation::deleteComment");
    }
} 