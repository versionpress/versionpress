<?php

namespace VersionPress\Tests\End2End\Users;

use VersionPress\Tests\End2End\Utils\End2EndTestCase;
use VersionPress\Tests\Utils\CommitAsserter;
use VersionPress\Tests\Utils\DBAsserter;

class UsersTest extends End2EndTestCase
{

    /** @var IUsersTestWorker */
    private static $worker;

    private static $testUser = [
        'login' => 'JohnTester',
        'email' => 'john.tester@example.com',
        'password' => 'password',
        'first-name' => 'John',
        'last-name' => 'Tester',
    ];

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$worker->setTestUser(self::$testUser);
    }

    /**
     * @test
     * @testdox Adding users creates 'user/create' action
     */
    public function addingUserCreatesUserCreateAction()
    {
        self::$worker->prepare_createUser();

        $this->commitAsserter->reset();

        self::$worker->createUser();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("user/create");
        $this->commitAsserter->assertCommitTag("VP-User-Login", self::$testUser['login']);
        $this->commitAsserter->assertCommitPath("A", "%vpdb%/users/%VPID%.ini");
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Editing user's email creates 'user/update' action
     * @depends addingUserCreatesUserCreateAction
     */
    public function editingUserCreatesUserEditAction()
    {
        self::$worker->prepare_editUser();

        $this->commitAsserter->reset();

        self::$worker->editUser();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("user/update");
        $this->commitAsserter->assertCommitTag("VP-User-Login", self::$testUser['login']);
        $this->commitAsserter->assertCommitPath("M", "%vpdb%/users/%VPID%.ini");
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Editing user's name creates 'usermeta/update' action
     * @depends addingUserCreatesUserCreateAction
     */
    public function editingUsermetaCreatesUsermetaEditAction()
    {
        self::$worker->prepare_editUsermeta();

        $this->commitAsserter->reset();

        self::$worker->editUsermeta();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("usermeta/update");
        $this->commitAsserter->assertCommitTag("VP-User-Login", self::$testUser['login']);
        $this->commitAsserter->assertCommitPath("M", "%vpdb%/users/%VPID(VP-User-Id)%.ini");
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Deleting user creates 'user/delete' action
     * @depends editingUserCreatesUserEditAction
     */
    public function deletingUserCreatesUserDeleteAction()
    {
        self::$worker->prepare_deleteUser();

        $this->commitAsserter->reset();

        self::$worker->deleteUser();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("user/delete");
        $this->commitAsserter->assertCommitTag("VP-User-Login", self::$testUser['login']);
        $this->commitAsserter->assertCommitPath("D", "%vpdb%/users/%VPID%.ini");
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Editing multiple users creates bulk action
     */
    public function editingMultipleUsersCreatesBulkAction()
    {
        self::$worker->prepare_editTwoUsers();

        $this->commitAsserter->reset();

        self::$worker->editTwoUsers();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertBulkAction("user/update", 2);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Deleting multiple users creates bulk action
     */
    public function deletingMultipleUsersCreatesBulkAction()
    {
        self::$worker->prepare_deleteTwoUsers();

        $this->commitAsserter->reset();

        self::$worker->deleteTwoUsers();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertBulkAction("user/delete", 2);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }


    /**
     * @test
     * @testdox Editing multiple usermeta creates bulk action
     */
    public function editingMultipleUsermetaCreatesBulkAction()
    {
        self::$worker->prepare_editTwoUsermeta();

        $this->commitAsserter->reset();

        self::$worker->editTwoUsermeta();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertBulkAction("usermeta/update", 2);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Deleting usermeta creates multiple usermeta creates 'usermeta/delete' action
     */
    public function deletingUsermetaCreatesUsermetaDeleteAction()
    {
        self::$worker->prepare_deleteUsermeta();

        $this->commitAsserter->reset();

        self::$worker->deleteUsermeta();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("usermeta/delete");
        $this->commitAsserter->assertCommitTag("VP-User-Login", self::$testUser['login']);
        $this->commitAsserter->assertCommitPath("M", "%vpdb%/users/%VPID(VP-User-Id)%.ini");
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        self::$worker->tearDownAfterClass();
    }
}
