<?php

namespace VersionPress\Tests\End2End\Users;

use Nette\Utils\Random;
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
        $user = json_decode($this->wpAutomation->runWpCliCommand('user', 'get', array($this->userId)));
        $this->originalEmail = $user->email;
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
}