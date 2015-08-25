<?php

namespace VersionPress\Tests\End2End\Users;

use Nette\Utils\Random;
use Nette\Utils\Strings;
use VersionPress\Tests\End2End\Utils\SeleniumWorker;

class UsersTestSeleniumWorker extends SeleniumWorker implements IUsersTestWorker {

    private $testUser;
    private $userIds = array();

    public function setTestUser($testUser) {
        $this->testUser = $testUser;
    }

    public function prepare_createUser() {
        $this->url('wp-admin/user-new.php');
    }

    public function createUser() {
        $this->byCssSelector('#user_login')->value($this->testUser['login']);
        $this->byCssSelector('#email')->value($this->testUser['email']);

        if ($this->isWpVersionLowerThan('4.3-alpha1')) { // WP 4.3 uses auto-generated passwords
            $this->byCssSelector('#pass1')->value($this->testUser['password']);
            $this->byCssSelector('#pass2')->value($this->testUser['password']);
        }

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

    public function prepare_editTwoUsers() {
        throw new \PHPUnit_Framework_SkippedTestError("There is no way to edit multiple users in the GUI");
    }

    public function editTwoUsers() {
    }


    public function prepare_deleteTwoUsers() {
        $this->userIds = array();
        $this->userIds[] = self::$wpAutomation->createUser($this->prepareTestUser());
        $this->userIds[] = self::$wpAutomation->createUser($this->prepareTestUser());
    }

    public function deleteTwoUsers() {
        $this->url('wp-admin/users.php');

        foreach ($this->userIds as $id) {
            $this->jsClick("#user-$id .check-column input[type=checkbox]");
        }

        $this->select($this->byId('bulk-action-selector-top'))->selectOptionByValue('delete');
        $this->jsClickAndWait('#doaction');
        $this->byCssSelector('#delete_option0')->click();
        $this->byCssSelector('#updateusers')->submit();
        $this->waitAfterRedirect();
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
        $this->userIds = self::$wpAutomation->createUser($this->prepareTestUser());

        $this->url('wp-admin/users.php');
        $this->jsClick("#user-{$this->userIds} .username a");
        $this->waitAfterRedirect();
    }

    public function editTwoUsermeta() {
        $this->byCssSelector('#first_name')->value(Random::generate());
        $this->byCssSelector('#last_name')->value(Random::generate());

        $this->byCssSelector('#your-profile')->submit();
        $this->waitAfterRedirect();
    }

    public function tearDownAfterClass() {
        $users = json_decode(self::$wpAutomation->runWpCliCommand('user', 'list', array('format' => 'json')));
        $userLogins = array_map(function ($user) { return $user->user_login; }, $users);
        $usersForBulkTests = array_filter($userLogins, function ($login) { return Strings::startsWith($login, 'bulk_'); });
        self::$wpAutomation->runWpCliCommand('user', 'delete', array_merge($usersForBulkTests, array('yes' => null)));
    }
}