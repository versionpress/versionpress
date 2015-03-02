<?php

namespace VersionPress\Tests\End2End\Users;

use VersionPress\Tests\End2End\Utils\ITestWorker;

interface IUsersTestWorker extends ITestWorker {

    public function setTestUser($testUser);

    public function prepare_createUser();
    public function createUser();

    public function prepare_editUser();
    public function editUser();

    public function prepare_editUsermeta();
    public function editUsermeta();

    public function prepare_deleteUser();
    public function deleteUser();
}