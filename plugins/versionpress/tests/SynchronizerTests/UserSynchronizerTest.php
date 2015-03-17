<?php

namespace VersionPress\Tests\SynchronizerTests;

use VersionPress\Storages\UserStorage;
use VersionPress\Synchronizers\Synchronizer;
use VersionPress\Synchronizers\UsersSynchronizer;
use VersionPress\Tests\SynchronizerTests\Utils\EntityUtils;
use VersionPress\Tests\Utils\DBAsserter;

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
        $user = EntityUtils::prepareUser();
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
        $this->storage->save(EntityUtils::prepareUser($this->vpId, array('user_email' => 'changed.email@example.com')));
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer removes deleted user from the database
     */
    public function synchronizerRemovesDeletedUserFromDatabase() {
        $this->storage->delete(EntityUtils::prepareUser($this->vpId));
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }
}