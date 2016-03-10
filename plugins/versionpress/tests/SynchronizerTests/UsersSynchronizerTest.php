<?php

namespace VersionPress\Tests\SynchronizerTests;

use VersionPress\Storages\UserStorage;
use VersionPress\Synchronizers\Synchronizer;
use VersionPress\Synchronizers\UsersSynchronizer;
use VersionPress\Tests\SynchronizerTests\Utils\EntityUtils;
use VersionPress\Tests\Utils\DBAsserter;
use VersionPress\Utils\AbsoluteUrlReplacer;

class UsersSynchronizerTest extends SynchronizerTestCase {
    /** @var UserStorage */
    private $storage;
    /** @var UsersSynchronizer */
    private $synchronizer;
    private static $vpId;

    protected function setUp() {
        parent::setUp();
        $this->storage = self::$storageFactory->getStorage('user');
        $this->synchronizer = new UsersSynchronizer($this->storage, self::$wpdb, self::$schemaInfo, self::$urlReplacer, self::$shortcodesReplacer);
    }

    /**
     * @test
     * @testdox Synchronizer adds new user to the database
     */
    public function synchronizerAddsNewUserToDatabase() {
        $this->createUser();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer updates changed user in the database
     */
    public function synchronizerUpdatesChangedUserInDatabase() {
        $this->editUser();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer replaces absolute URLs
     */
    public function synchronizerReplacesAbsoluteUrls() {
        $this->editUser('user_url', AbsoluteUrlReplacer::PLACEHOLDER);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer removes deleted user from the database
     */
    public function synchronizerRemovesDeletedUserFromDatabase() {
        $this->deleteUser();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer adds new user to the database (selective synchronization)
     */
    public function synchronizerAddsNewUserToDatabase_selective() {
        $entitiesToSynchronize = $this->createUser();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer updates changed user in the database (selective synchronization)
     */
    public function synchronizerUpdatesChangedUserInDatabase_selective() {
        $entitiesToSynchronize = $this->editUser();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer removes deleted user from the database (selective synchronization)
     */
    public function synchronizerRemovesDeletedUserFromDatabase_selective() {
        $entitiesToSynchronize = $this->deleteUser();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        DBAsserter::assertFilesEqualDatabase();
    }

    private function createUser() {
        $user = EntityUtils::prepareUser();
        self::$vpId = $user['vp_id'];
        $this->storage->save($user);
        return array(array('vp_id' => self::$vpId, 'parent' => self::$vpId));
    }

    private function editUser($key = 'user_email', $value = 'changed.email@example.com') {
        $this->storage->save(EntityUtils::prepareUser(self::$vpId, array($key => $value)));
        return array(array('vp_id' => self::$vpId, 'parent' => self::$vpId));
    }

    private function deleteUser() {
        $this->storage->delete(EntityUtils::prepareUser(self::$vpId));
        return array(array('vp_id' => self::$vpId, 'parent' => self::$vpId));
    }
}
