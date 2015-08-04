<?php

namespace VersionPress\Tests\SynchronizerTests;

use Nette\Utils\Random;
use VersionPress\Storages\PostStorage;
use VersionPress\Storages\TermsStorage;
use VersionPress\Storages\TermTaxonomyStorage;
use VersionPress\Storages\UserStorage;
use VersionPress\Synchronizers\PostsSynchronizer;
use VersionPress\Synchronizers\Synchronizer;
use VersionPress\Synchronizers\TermsSynchronizer;
use VersionPress\Synchronizers\TermTaxonomySynchronizer;
use VersionPress\Synchronizers\UsersSynchronizer;
use VersionPress\Tests\SynchronizerTests\Utils\EntityUtils;
use VersionPress\Tests\Utils\DBAsserter;
use VersionPress\Utils\IdUtil;

class PostSynchronizerTest extends SynchronizerTestCase {
    /** @var PostStorage */
    private $storage;
    /** @var UserStorage */
    private $userStorage;
    /** @var TermsStorage */
    private $termStorage;
    /** @var TermTaxonomyStorage */
    private $termTaxonomyStorage;

    /** @var PostsSynchronizer */
    private $synchronizer;
    /** @var UsersSynchronizer */
    private $userSynchronizer;
    /** @var TermsSynchronizer */
    private $termSynchronizer;
    /** @var TermTaxonomySynchronizer */
    private $termTaxonomySynchronizer;

    private static $authorVpId;
    private static $vpId;
    private static $categoryVpId;
    private static $categoryTaxonomyVpId;

    protected function setUp() {
        parent::setUp();
        $this->storage = self::$storageFactory->getStorage('post');
        $this->userStorage = self::$storageFactory->getStorage('user');
        $this->termStorage = self::$storageFactory->getStorage('term');
        $this->termTaxonomyStorage = self::$storageFactory->getStorage('term_taxonomy');

        $this->synchronizer = new PostsSynchronizer($this->storage, self::$wpdb, self::$schemaInfo, self::$urlReplacer);
        $this->userSynchronizer = new UsersSynchronizer($this->userStorage, self::$wpdb, self::$schemaInfo, self::$urlReplacer);
        $this->termSynchronizer = new TermsSynchronizer($this->termStorage, self::$wpdb, self::$schemaInfo, self::$urlReplacer);
        $this->termTaxonomySynchronizer = new TermTaxonomySynchronizer($this->termTaxonomyStorage, self::$wpdb, self::$schemaInfo, self::$urlReplacer);
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

    /**
     * @test
     * @testdox Synchronizer synchronizes term relationships (changes category)
     */
    public function synchronizerSynchronizesTermRelationships_changeCategory() {
        $this->createPost();
        $this->changeCategory();
        $this->userSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->termSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->termTaxonomySynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer synchronizes term relationships (removes category)
     */
    public function synchronizerSynchronizesTermRelationships_removeCategory() {
        $this->removeCategory();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->termTaxonomySynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->termSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->userSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer synchronizes term relationships (changes category, selective synchronization)
     */
    public function synchronizerSynchronizesTermRelationships_changeCategory_selective() {
        $entitiesToSynchronize = $this->createPost();
        $entitiesToSynchronize = array_merge($this->changeCategory(), $entitiesToSynchronize);
        $this->userSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        $this->termSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->termTaxonomySynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer synchronizes term relationships (removes category, selective synchronization)
     */
    public function synchronizerSynchronizesTermRelationships_removeCategory_selective() {
        $entitiesToSynchronize = $this->removeCategory();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        $this->termTaxonomySynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->termSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
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

    private function changeCategory() {
        $categoryTerm = EntityUtils::prepareTerm(null, 'Some category', 'some-category');
        self::$categoryVpId = $categoryTerm['vp_id'];
        $categoryTaxonomy = EntityUtils::prepareTermTaxonomy(null, self::$categoryVpId, 'category', 'Some description');
        self::$categoryTaxonomyVpId = $categoryTaxonomy['vp_id'];

        $this->termStorage->save($categoryTerm);
        $this->termTaxonomyStorage->save($categoryTaxonomy);

        $this->storage->save(EntityUtils::preparePost(self::$vpId, null, array('vp_term_taxonomy' => array(self::$categoryTaxonomyVpId))));

        return array(
            array('vp_id' => self::$categoryVpId, 'parent' => self::$categoryVpId),
            array('vp_id' => self::$categoryTaxonomyVpId, 'parent' => self::$categoryVpId),
            array('vp_id' => self::$vpId, 'parent' => self::$vpId),
        );
    }

    private function removeCategory() {
        $this->storage->delete(EntityUtils::preparePost(self::$vpId));
        $this->termTaxonomyStorage->delete(EntityUtils::prepareTermTaxonomy(self::$categoryTaxonomyVpId, self::$categoryVpId));
        $this->termStorage->delete(EntityUtils::prepareTerm(self::$categoryVpId));
        $this->userStorage->delete(EntityUtils::prepareUser(self::$authorVpId));

        return array(
            array('vp_id' => self::$vpId, 'parent' => self::$vpId),
            array('vp_id' => self::$categoryTaxonomyVpId, 'parent' => self::$categoryVpId),
            array('vp_id' => self::$categoryVpId, 'parent' => self::$categoryVpId),
            array('vp_id' => self::$authorVpId, 'parent' => self::$authorVpId),
        );
    }
}