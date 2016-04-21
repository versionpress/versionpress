<?php

namespace VersionPress\Tests\End2End\Users;

use VersionPress\Tests\End2End\Utils\ITestWorker;

interface IUsersTestWorker extends ITestWorker
{

    public function setTestUser($testUser);

    public function prepare_createUser();

    public function createUser();

    public function prepare_editUser();

    public function editUser();

    public function prepare_editUsermeta();

    public function editUsermeta();

    public function prepare_deleteUser();

    public function deleteUser();

    public function prepare_editTwoUsers();

    public function editTwoUsers();

    public function prepare_deleteTwoUsers();

    public function deleteTwoUsers();

    public function prepare_editTwoUsermeta();

    public function editTwoUsermeta();

    public function prepare_deleteUsermeta();

    public function deleteUsermeta();

    public function tearDownAfterClass();
}
