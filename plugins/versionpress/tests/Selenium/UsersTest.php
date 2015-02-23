<?php

namespace VersionPress\Tests\Selenium;

use VersionPress\Tests\Utils\CommitAsserter;

class UsersTest extends SeleniumTestCase {
    private $testUser = array(
        'login' => 'JohnTester',
        'email' => 'john.tester@example.com',
        'password' => 'password',
        'first-name' => 'John',
        'last-name' => 'Tester',
    );

    /**
     * @test
     * @testdox Adding users creates 'user/create' action
     */
    public function addingUserCreatesUserCreateAction() {
        $this->loginIfNecessary();
        $this->url('wp-admin/user-new.php');
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->byCssSelector('#user_login')->value($this->testUser['login']);
        $this->byCssSelector('#email')->value($this->testUser['email']);
        $this->byCssSelector('#pass1')->value($this->testUser['password']);
        $this->byCssSelector('#pass2')->value($this->testUser['password']);
        $this->byCssSelector('#createuser')->submit();
        $this->waitAfterRedirect();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("user/create");
        $commitAsserter->assertCommitTag("VP-User-Login", $this->testUser['login']);
        $commitAsserter->assertCommitPath("A", "%vpdb%/users/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();
    }

    /**
     * @test
     * @testdox Editing user's email creates 'user/edit' action
     * @depends addingUserCreatesUserCreateAction
     */
    public function editingUserCreatesUserEditAction() {

        $this->url('wp-admin/users.php');
        $this->jsClick(".username a:contains('{$this->testUser['login']}')");
        $this->waitAfterRedirect();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        $emailInput = $this->byCssSelector('#email');
        $emailInput->clear();
        $emailInput->value('edit.' . $this->testUser['email']);

        $this->byCssSelector('#your-profile')->submit();
        $this->waitAfterRedirect();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("user/edit");
        $commitAsserter->assertCommitTag("VP-User-Login", $this->testUser['login']);
        $commitAsserter->assertCommitPath("M", "%vpdb%/users/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();
    }

    /**
     * @test
     * @testdox Editing user's name creates 'usermeta/edit' action
     * @depends addingUserCreatesUserCreateAction
     */
    public function editingUsermetaCreatesUsermetaEditAction() {

        $this->url('wp-admin/users.php');
        $this->jsClick(".username a:contains('{$this->testUser['login']}')");
        $this->waitAfterRedirect();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->byCssSelector('#first_name')->value($this->testUser['first-name']);
        $this->byCssSelector('#last_name')->value($this->testUser['last-name']);

        $this->byCssSelector('#your-profile')->submit();
        $this->waitAfterRedirect();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("usermeta/edit");
        $commitAsserter->assertCommitTag("VP-User-Login", $this->testUser['login']);
        $commitAsserter->assertCommitPath("M", "%vpdb%/users/%VPID(VP-User-Id)%.ini");
        $commitAsserter->assertCleanWorkingDirectory();
    }

    /**
     * @test
     * @testdox Deleting user creates 'user/delete' action
     * @depends editingUserCreatesUserEditAction
     */
    public function deletingUserCreatesUserDeleteAction() {

        $this->url('wp-admin/users.php');
        $this->executeScript("jQuery(\"a:contains('{$this->testUser['login']}')\").parents('td').find('.delete a')[0].click()");
        $this->waitAfterRedirect();

        $commitAsserter = new CommitAsserter($this->gitRepository);
        $this->byCssSelector('#delete_option0')->click();
        $this->byCssSelector('#updateusers')->submit();
        $this->waitAfterRedirect();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("user/delete");
        $commitAsserter->assertCommitTag("VP-User-Login", $this->testUser['login']);
        $commitAsserter->assertCommitPath("D", "%vpdb%/users/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();
    }
}