<?php

namespace VersionPress\Tests\SynchronizerTests;

use VersionPress\Storages\UserStorage;
use VersionPress\Synchronizers\Synchronizer;
use VersionPress\Synchronizers\UsersSynchronizer;
use VersionPress\Tests\Utils\DBAsserter;
use VersionPress\Utils\IdUtil;

class UserSynchronizerTest extends SynchronizerTestCase {
    /** @var UserStorage */
    private $storage;
    /** @var UsersSynchronizer */
    private $synchronizer;
    private $vpId;

    protected function setUp() {
        parent::setUp();
        $this->storage = self::$storageFactory->getStorage('user');
        $this->synchronizer = new UsersSynchronizer($this->storage, self::$wpdb, self::$schemaInfo);
    }

    /**
     * @test
     * @testdox Synchronizer adds new user to the database
     */
    public function synchronizerAddsNewUserToDatabase() {
        $user = $this->prepareUser();
        $this->vpId = $user['vp_id'];
        $this->storage->save($user);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer updates changed user in the database
     */
    public function synchronizerUpdatesChangedUserInDatabase() {
        $this->storage->save($this->prepareUser($this->vpId, array('user_email' => 'changed.email@example.com')));
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer removes deleted user from the database
     */
    public function synchronizerRemovesDeletedUserFromDatabase() {
        $this->storage->delete($this->prepareUser($this->vpId));
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    private function prepareUser($vpId = null, $userValues = array()) {
        if ($vpId === null) {
            $vpId = IdUtil::newId();
        }

        return array_merge(array(
            "user_login" => "JohnTester",
            "user_pass" => '$P$B3hfEaUjEIkzHqzDHQ5kCALiUGv3rt1',
            "user_nicename" => "JohnTester",
            "user_email" => "johntester@example.com",
            "user_url" => "",
            "user_registered" => "2015-02-02 14:19:58",
            "user_activation_key" => "",
            "user_status" => 0,
            "display_name" => "JohnTester",
            "vp_id" => $vpId,
        ), $userValues);
    }
}