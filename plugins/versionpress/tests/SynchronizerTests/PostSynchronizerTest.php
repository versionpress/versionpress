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
        $this->createPost();
        $this->userSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);

        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer updates changed post in the database
     */
    public function synchronizerUpdatesChangedPostInDatabase() {
        $this->editPost();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer removes deleted post from the database
     */
    public function synchronizerRemovesDeletedPostFromDatabase() {
        $this->deletePost();

        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->userSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer adds new post to the database (selective synchronization)
     */
    public function synchronizerAddsNewPostToDatabase_selective() {
        $entitiesToSynchronize = $this->createPost();
        $this->userSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);

        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer updates changed post in the database (selective synchronization)
     */
    public function synchronizerUpdatesChangedPostInDatabase_selective() {
        $entitiesToSynchronize = $this->editPost();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer removes deleted post from the database (selective synchronization)
     */
    public function synchronizerRemovesDeletedPostFromDatabase_selective() {
        $entitiesToSynchronize = $this->deletePost();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        $this->userSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        DBAsserter::assertFilesEqualDatabase();
    }

    private function createPost() {
        $author = EntityUtils::prepareUser();
        self::$authorVpId = $author['vp_id'];
        $this->userStorage->save($author);
        $post = EntityUtils::preparePost(null, self::$authorVpId);
        self::$vpId = $post['vp_id'];
        $this->storage->save($post);

        return array(
            array('vp_id' => self::$authorVpId, 'parent' => self::$authorVpId),
            array('vp_id' => self::$vpId, 'parent' => self::$vpId),
        );
    }

    private function editPost() {
        $this->storage->save(EntityUtils::preparePost(self::$vpId, null, array('post_status' => 'trash')));
        return array(
            array('vp_id' => self::$vpId, 'parent' => self::$vpId),
        );
    }

    private function deletePost() {
        $this->storage->delete(EntityUtils::preparePost(self::$vpId));
        $this->userStorage->delete(EntityUtils::prepareUser(self::$authorVpId));

        return array(
            array('vp_id' => self::$authorVpId, 'parent' => self::$authorVpId),
            array('vp_id' => self::$vpId, 'parent' => self::$vpId),
        );
    }
}