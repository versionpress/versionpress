<?php

namespace VersionPress\Tests\End2End\Users;

use Nette\Utils\Random;
use Nette\Utils\Strings;
use VersionPress\Tests\End2End\Utils\WpCliWorker;

class UsersTestWpCliWorker extends WpCliWorker implements IUsersTestWorker {

    private $testUser;
    private $userId;
    private $originalEmail;
    private $originalFirstName;

    public function setTestUser($testUser) {
        $this->testUser = array(
            'user_login' => $testUser['login'],
            'user_email' => $testUser['email'],
            'user_pass' => $testUser['password'],
            'first_name' => $testUser['first-name'],
            'last_name' => $testUser['last-name'],
        );
    }

    public function prepare_createUser() {
    }

    public function createUser() {
        $this->userId = $this->wpAutomation->createUser($this->testUser);
    }

    public function prepare_editUser() {
        $user = json_decode($this->wpAutomation->runWpCliCommand('user', 'get', array($this->userId, 'format' => 'json')));
        $this->originalEmail = $user->user_email;
        $this->wpAutomation->editUser($this->userId, array('user_email' => 'random.email' . Random::generate() . '@example.com'));
    }

    public function editUser() {
        $this->wpAutomation->editUser($this->userId, array('user_email' => $this->originalEmail));
    }

    public function prepare_editUsermeta() {
        $this->originalFirstName = trim($this->wpAutomation->runWpCliCommand('user', 'meta', array('get', $this->userId, 'first_name')));
        $this->wpAutomation->runWpCliCommand('user', 'meta', array('update', $this->userId, 'first_name', 'Random First Name ' . Random::generate()));
    }

    public function editUsermeta() {
        $this->wpAutomation->runWpCliCommand('user', 'meta', array('update', $this->userId, 'first_name', $this->originalFirstName));
    }

    public function prepare_deleteUser() {
    }

    public function deleteUser() {
        $this->wpAutomation->deleteUser($this->userId);
    }

    public function prepare_editTwoUsers() {
        $this->userId = array();
        $this->userId[] = $this->wpAutomation->createUser($this->prepareTestUser());
        $this->userId[] = $this->wpAutomation->createUser($this->prepareTestUser());
    }

    public function editTwoUsers() {
        $this->wpAutomation->runWpCliCommand('user', 'update', array_merge($this->userId, array('display_name' => 'changed name')));
    }

    public function prepare_deleteTwoUsers() {
        $this->userId = array();
        $this->userId[] = $this->wpAutomation->createUser($this->prepareTestUser());
        $this->userId[] = $this->wpAutomation->createUser($this->prepareTestUser());
    }

    public function deleteTwoUsers() {
        $this->wpAutomation->runWpCliCommand('user', 'delete', array_merge($this->userId, array('yes' => null)));
    }

    private function prepareTestUser() {
        return array(
            'user_login' => 'bulk_' . Random::generate(),
            'user_email' => 'bulk.' . Random::generate() . '@example.com',
            'user_pass' => Random::generate(),
            'first_name' => Random::generate(),
            'last_name' => Random::generate(),
        );
    }

    public function prepare_editTwoUsermeta() {
        throw new \PHPUnit_Framework_SkippedTestError("There is no way to change multiple usermeta using WP-CLI");
    }

    public function editTwoUsermeta() {
    }

    public function tearDownAfterClass() {
        $users = json_decode($this->wpAutomation->runWpCliCommand('user', 'list', array('format' => 'json')));
        $userLogins = array_map(function ($user) { return $user->user_login; }, $users);
        $usersForBulkTests = array_filter($userLogins, function ($login) { return Strings::startsWith($login, 'bulk_'); });
        $this->wpAutomation->runWpCliCommand('user', 'delete', array_merge($usersForBulkTests, array('yes' => null)));
    }
}