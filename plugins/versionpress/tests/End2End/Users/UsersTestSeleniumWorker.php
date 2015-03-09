<?php

namespace VersionPress\Tests\End2End\Users;

use VersionPress\Tests\End2End\Utils\SeleniumWorker;

class UsersTestSeleniumWorker extends SeleniumWorker implements IUsersTestWorker {

    private $testUser;

    public function setTestUser($testUser) {
        $this->testUser = $testUser;
    }

    public function prepare_createUser() {
        $this->url('wp-admin/user-new.php');
    }

    public function createUser() {
        $this->byCssSelector('#user_login')->value($this->testUser['login']);
        $this->byCssSelector('#email')->value($this->testUser['email']);
        $this->byCssSelector('#pass1')->value($this->testUser['password']);
        $this->byCssSelector('#pass2')->value($this->testUser['password']);
        $this->byCssSelector('#createuser')->submit();
        $this->waitAfterRedirect();
    }

    public function prepare_editUser() {
        $this->url('wp-admin/users.php');
        $this->jsClick(".username a:contains('{$this->testUser['login']}')");
        $this->waitAfterRedirect();
    }

    public function editUser() {
        $emailInput = $this->byCssSelector('#email');
        $emailInput->clear();
        $emailInput->value('edit.' . $this->testUser['email']);

        $this->byCssSelector('#your-profile')->submit();
        $this->waitAfterRedirect();
    }

    public function prepare_editUsermeta() {
        $this->url('wp-admin/users.php');
        $this->jsClick(".username a:contains('{$this->testUser['login']}')");
        $this->waitAfterRedirect();
    }

    public function editUsermeta() {
        $this->byCssSelector('#first_name')->value($this->testUser['first-name']);
        $this->byCssSelector('#last_name')->value($this->testUser['last-name']);

        $this->byCssSelector('#your-profile')->submit();
        $this->waitAfterRedirect();
    }

    public function prepare_deleteUser() {
        $this->url('wp-admin/users.php');
        $this->executeScript("jQuery(\"a:contains('{$this->testUser['login']}')\").parents('td').find('.delete a')[0].click()");
        $this->waitAfterRedirect();
    }

    public function deleteUser() {
        $this->byCssSelector('#delete_option0')->click();
        $this->byCssSelector('#updateusers')->submit();
        $this->waitAfterRedirect();
    }
}