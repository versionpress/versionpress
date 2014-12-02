<?php

class UserTest extends EndToEndTestCase {

    private $someUser = array(
        "user_login" => "test-versionpress",
        "user_email" => "test.versionpress@example.com",
        "user_registered" => "2011-12-13 14:15:16",
        "display_name" => "test",
    );

    public function testNewUser() {
        WpAutomation::createUser($this->someUser);

        $lastCommit = $this->getLastCommit();
        $comitAction = $lastCommit->getMessage()->getVersionPressTag(TrackedChangeInfo::ACTION_TAG);
        $loginInTag = $lastCommit->getMessage()->getVersionPressTag(UserChangeInfo::USER_LOGIN);
        $this->assertStringStartsWith("user/create", $comitAction);
        $this->assertEquals($this->someUser["user_login"], $loginInTag);

        list($_, $__, $commentVpId) = explode("/", $comitAction, 3);
        $commitedPost = $this->getCommitedEntity($commentVpId);
        $this->assertEntityEquals($this->someUser, $commitedPost);
        $this->assertIdExistsInDatabase($commentVpId);
    }

    public function testEditUser() {
        $newUser = $this->someUser;
        $newUser["user_login"] = "test-versionpress-edit";
        $newUser["user_email"] = "test.versionpress.edit@example.com";
        $changes = array(
            "user_email" => "agilio@example.com"
        );
        $this->assertUserEditation($newUser, $changes);
    }

    public function testDeleteUser() {
        $newUser = $this->someUser;
        $newUser["user_login"] = "test-versionpress-delete";
        $newUser["user_email"] = "test.versionpress.delete@example.com";

        $this->assertUserDeletion($newUser);
    }

    public function testEditUserMeta() {
        $newUser = $this->someUser;
        $newUser["user_login"] = "test-versionpress-editmeta";
        $newUser["user_email"] = "test.versionpress.editmeta@example.com";

        $changedUserMeta = "first_name";
        $userMetaValue = "Edited name";

        $id = WpAutomation::createUser($newUser);
        $creationCommit = $this->getLastCommit();
        $createdUserVpId = $this->getVpIdFromCommit($creationCommit);

        WpAutomation::editUserMeta($id, $changedUserMeta, $userMetaValue);
        $editationCommit = $this->getLastCommit();
        $this->assertStringStartsWith(
            "usermeta/edit",
            $editationCommit->getMessage()->getVersionPressTag(TrackedChangeInfo::ACTION_TAG),
            "Expected another action"
        );

        $commitedEntity = $this->getCommitedEntity($createdUserVpId);
        $newEntity = array_merge($newUser, array($changedUserMeta => $userMetaValue));
        $this->assertEntityEquals($newEntity, $commitedEntity);
    }

    protected function getCommitedEntity($vpId) {
        $path = self::$config->getSitePath() . '/wp-content/vpdb/users.ini';
        $users = IniSerializer::deserialize(file_get_contents($path), true);
        return $users[$vpId];
    }

    private function assertUserEditation($user, $changes) {
        $this->assertEditation($user, $changes, "user/edit", "WpAutomation::createUser", "WpAutomation::editUser");
    }

    private function assertUserDeletion($user) {
        $this->assertDeletion($user, "user", "WpAutomation::createUser", "WpAutomation::deleteUser");
    }
}
