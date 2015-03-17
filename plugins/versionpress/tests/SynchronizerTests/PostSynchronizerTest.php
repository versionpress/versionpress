<?php

namespace VersionPress\Tests\SynchronizerTests;

use Nette\Utils\Random;
use VersionPress\Storages\PostStorage;
use VersionPress\Storages\UserStorage;
use VersionPress\Synchronizers\PostsSynchronizer;
use VersionPress\Synchronizers\Synchronizer;
use VersionPress\Synchronizers\UsersSynchronizer;
use VersionPress\Tests\SynchronizerTests\Utils\EntityUtils;
use VersionPress\Tests\Utils\DBAsserter;
use VersionPress\Utils\IdUtil;

class PostSynchronizerTest extends SynchronizerTestCase {
    /** @var PostStorage */
    private $storage;
    /** @var UserStorage */
    private $userStorage;
    /** @var PostsSynchronizer */
    private $synchronizer;
    /** @var UsersSynchronizer */
    private $userSynchronizer;
    private static $authorVpId;
    private static $vpId;

    protected function setUp() {
        parent::setUp();
        $this->storage = self::$storageFactory->getStorage('post');
        $this->userStorage = self::$storageFactory->getStorage('user');
        $this->synchronizer = new PostsSynchronizer($this->storage, self::$wpdb, self::$schemaInfo);
        $this->userSynchronizer = new UsersSynchronizer($this->userStorage, self::$wpdb, self::$schemaInfo);
    }

    /**
     * @test
     * @testdox Synchronizer adds new post to the database
     */
    public function synchronizerAddsNewPostToDatabase() {
        $author = EntityUtils::prepareUser();
        self::$authorVpId = $author['vp_id'];
        $this->userStorage->save($author);
        $post = EntityUtils::preparePost(null, self::$authorVpId);
        self::$vpId = $post['vp_id'];
        $this->storage->save($post);

        $this->userSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);

        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer updates changed post in the database
     */
    public function synchronizerUpdatesChangedPostInDatabase() {
        $this->storage->save(EntityUtils::preparePost(self::$vpId, null, array('post_status' => 'trash')));
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer removes deleted post from the database
     */
    public function synchronizerRemovesDeletedPostFromDatabase() {
        $this->storage->delete(EntityUtils::preparePost(self::$vpId));
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();

        $this->userStorage->delete(EntityUtils::prepareUser(self::$authorVpId));
        $this->userSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }
}