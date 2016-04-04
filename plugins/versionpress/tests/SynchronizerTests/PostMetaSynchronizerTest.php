<?php

namespace VersionPress\Tests\SynchronizerTests;

use VersionPress\Storages\PostMetaStorage;
use VersionPress\Storages\PostStorage;
use VersionPress\Storages\UserStorage;
use VersionPress\Synchronizers\PostMetaSynchronizer;
use VersionPress\Synchronizers\PostsSynchronizer;
use VersionPress\Synchronizers\Synchronizer;
use VersionPress\Synchronizers\UsersSynchronizer;
use VersionPress\Tests\SynchronizerTests\Utils\EntityUtils;
use VersionPress\Tests\Utils\DBAsserter;
use VersionPress\Utils\AbsoluteUrlReplacer;

class PostMetaSynchronizerTest extends SynchronizerTestCase {
    /** @var PostMetaStorage */
    private $storage;
    /** @var PostStorage */
    private $postStorage;
    /** @var UserStorage */
    private $userStorage;
    /** @var PostMetaSynchronizer */
    private $synchronizer;
    /** @var PostsSynchronizer */
    private $postsSynchronizer;
    /** @var UsersSynchronizer */
    private $usersSynchronizer;
    private static $authorVpId;
    private static $postVpId;
    private static $post2VpId;
    private static $vpId;

    protected function setUp() {
        parent::setUp();
        $this->storage = self::$storageFactory->getStorage('postmeta');
        $this->postStorage = self::$storageFactory->getStorage('post');
        $this->userStorage = self::$storageFactory->getStorage('user');
        $this->synchronizer = new PostMetaSynchronizer($this->storage, self::$database, self::$schemaInfo->getEntityInfo('postmeta') ,self::$schemaInfo, self::$vpidRepository, self::$urlReplacer, self::$shortcodesReplacer);
        $this->postsSynchronizer = new PostsSynchronizer($this->postStorage, self::$database, self::$schemaInfo->getEntityInfo('post') ,self::$schemaInfo, self::$vpidRepository, self::$urlReplacer, self::$shortcodesReplacer);
        $this->usersSynchronizer = new UsersSynchronizer($this->userStorage, self::$database, self::$schemaInfo->getEntityInfo('user') ,self::$schemaInfo, self::$vpidRepository, self::$urlReplacer, self::$shortcodesReplacer);
    }

    /**
     * @test
     * @testdox Synchronizer adds new postmeta to the database
     */
    public function synchronizerAddsNewPostMetaToDatabase() {
        $this->createPostMeta();
        $this->usersSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->postsSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);

        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer updates changed postmeta in the database
     */
    public function synchronizerUpdatesChangedPostMetaInDatabase() {
        $this->editPostMeta('_thumbnail_id', self::$postVpId);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer replaces absolute URLs
     */
    public function synchronizerReplacesAbsoluteUrls() {
        $this->editPostMeta('some_meta', AbsoluteUrlReplacer::PLACEHOLDER);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer removes deleted postmeta from the database
     */
    public function synchronizerRemovesDeletedPostMetaFromDatabase() {
        $this->deletePostMeta();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->postsSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->usersSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer adds new postmeta to the database (selective synchronization)
     */
    public function synchronizerAddsNewPostMetaToDatabase_selective() {
        $entitiesToSynchronize = $this->createPostMeta();
        $this->usersSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        $this->postsSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);

        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer updates changed postmeta in the database (selective synchronization)
     */
    public function synchronizerUpdatesChangedPostMetaInDatabase_selective() {
        $entitiesToSynchronize = $this->editPostMeta('_thumbnail_id', self::$postVpId);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer removes deleted postmeta from the database (selective synchronization)
     */
    public function synchronizerRemovesDeletedPostMetaFromDatabase_selective() {
        $entitiesToSynchronize = $this->deletePostMeta();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        $this->postsSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        $this->usersSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        DBAsserter::assertFilesEqualDatabase();
    }

    private function createPostMeta() {
        $author = EntityUtils::prepareUser();
        self::$authorVpId = $author['vp_id'];
        $this->userStorage->save($author);

        $post = EntityUtils::preparePost(null, self::$authorVpId);
        self::$postVpId = $post['vp_id'];
        $this->postStorage->save($post);

        $post2 = EntityUtils::preparePost(null, self::$authorVpId);
        self::$post2VpId = $post2['vp_id'];
        $this->postStorage->save($post2);

        /**
         * This postmeta has a value reference to another post.
         * @see wordpress-schema.yml
         * @var array
         */
        $postmeta = EntityUtils::preparePostMeta(null, self::$postVpId, '_thumbnail_id', self::$post2VpId);
        self::$vpId = $postmeta['vp_id'];
        $this->storage->save($postmeta);

        return array(
            array('vp_id' => self::$authorVpId, 'parent' => self::$authorVpId),
            array('vp_id' => self::$postVpId, 'parent' => self::$postVpId),
            array('vp_id' => self::$post2VpId, 'parent' => self::$post2VpId),
            array('vp_id' => self::$vpId, 'parent' => self::$postVpId),
        );
    }

    private function editPostMeta($key, $value) {
        $this->storage->save(EntityUtils::preparePostMeta(self::$vpId, self::$postVpId, $key, $value));
        return array(
            array('vp_id' => self::$vpId, 'parent' => self::$postVpId),
        );
    }

    private function deletePostMeta() {
        $this->storage->delete(EntityUtils::preparePostMeta(self::$vpId, self::$postVpId));
        $this->postStorage->delete(EntityUtils::preparePost(self::$postVpId));
        $this->postStorage->delete(EntityUtils::preparePost(self::$post2VpId));
        $this->userStorage->delete(EntityUtils::prepareUser(self::$authorVpId));

        return array(
            array('vp_id' => self::$authorVpId, 'parent' => self::$authorVpId),
            array('vp_id' => self::$postVpId, 'parent' => self::$postVpId),
            array('vp_id' => self::$post2VpId, 'parent' => self::$post2VpId),
            array('vp_id' => self::$vpId, 'parent' => self::$postVpId),
        );
    }
}
