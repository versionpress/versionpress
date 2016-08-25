<?php

namespace VersionPress\Tests\SynchronizerTests;

use VersionPress\Storages\DirectoryStorage;
use VersionPress\Storages\MetaEntityStorage;
use VersionPress\Synchronizers\Synchronizer;
use VersionPress\Synchronizers\SynchronizerBase;
use VersionPress\Tests\SynchronizerTests\Utils\EntityUtils;
use VersionPress\Tests\Utils\DBAsserter;
use VersionPress\Utils\AbsoluteUrlReplacer;

class UserMetaSynchronizerTest extends SynchronizerTestCase
{
    /** @var MetaEntityStorage */
    private $storage;
    /** @var DirectoryStorage */
    private $userStorage;
    /** @var SynchronizerBase */
    private $synchronizer;
    /** @var SynchronizerBase */
    private $usersSynchronizer;
    private static $vpId;
    private static $userVpId;

    protected function setUp()
    {
        parent::setUp();
        $this->storage = self::$storageFactory->getStorage('usermeta');
        $this->userStorage = self::$storageFactory->getStorage('user');
        $this->synchronizer = new SynchronizerBase(
            $this->storage,
            self::$database,
            self::$schemaInfo->getEntityInfo('usermeta'),
            self::$schemaInfo,
            self::$vpidRepository,
            self::$urlReplacer,
            self::$shortcodesReplacer
        );
        $this->usersSynchronizer = new SynchronizerBase(
            $this->userStorage,
            self::$database,
            self::$schemaInfo->getEntityInfo('user'),
            self::$schemaInfo,
            self::$vpidRepository,
            self::$urlReplacer,
            self::$shortcodesReplacer
        );
    }

    /**
     * @test
     * @testdox Synchronizer adds new usermeta to the database
     */
    public function synchronizerAddsNewUserMetaToDatabase()
    {
        $this->createUserMeta();
        $this->usersSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer updates changed usermeta in the database
     */
    public function synchronizerUpdatesChangedUserMetaInDatabase()
    {
        $this->editUserMeta();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer replaces absolute URLs
     */
    public function synchronizerReplacesAbsoluteUrls()
    {
        $this->editUserMeta('some-meta', AbsoluteUrlReplacer::PLACEHOLDER);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer removes deleted usermeta from the database
     */
    public function synchronizerRemovesDeletedUserMetaFromDatabase()
    {
        $this->deleteUserMeta();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->usersSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer adds new usermeta to the database (selective synchronization)
     */
    public function synchronizerAddsNewUserMetaToDatabase_selective()
    {
        $entitiesToSynchronize = $this->createUserMeta();
        $this->usersSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer updates changed usermeta in the database (selective synchronization)
     */
    public function synchronizerUpdatesChangedUserMetaInDatabase_selective()
    {
        $entitiesToSynchronize = $this->editUserMeta();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer removes deleted usermeta from the database (selective synchronization)
     */
    public function synchronizerRemovesDeletedUserMetaFromDatabase_selective()
    {
        $entitiesToSynchronize = $this->deleteUserMeta();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        $this->usersSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        DBAsserter::assertFilesEqualDatabase();
    }

    private function createUserMeta()
    {
        $user = EntityUtils::prepareUser();
        self::$userVpId = $user['vp_id'];
        $this->userStorage->save($user);
        $usermeta = EntityUtils::prepareUserMeta(null, self::$userVpId, 'some-meta', 'some value');
        $this->storage->save($usermeta);

        self::$vpId = $usermeta['vp_id'];
        return [
            ['vp_id' => self::$vpId, 'parent' => self::$userVpId],
            ['vp_id' => self::$userVpId, 'parent' => self::$userVpId],
        ];
    }

    private function editUserMeta($key = 'some-meta', $value = 'another value')
    {
        $this->storage->save(EntityUtils::prepareUserMeta(self::$vpId, self::$userVpId, $key, $value));
        return [
            ['vp_id' => self::$vpId, 'parent' => self::$userVpId],
        ];
    }

    private function deleteUserMeta()
    {
        $this->storage->delete(EntityUtils::prepareUserMeta(self::$vpId, self::$userVpId));
        $this->userStorage->delete(EntityUtils::prepareUser(self::$userVpId));

        return [
            ['vp_id' => self::$vpId, 'parent' => self::$userVpId],
            ['vp_id' => self::$userVpId, 'parent' => self::$userVpId],
        ];
    }
}
