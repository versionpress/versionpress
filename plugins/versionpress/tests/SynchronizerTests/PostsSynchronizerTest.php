<?php

namespace VersionPress\Tests\SynchronizerTests;

use VersionPress\Storages\DirectoryStorage;
use VersionPress\Synchronizers\Synchronizer;
use VersionPress\Synchronizers\SynchronizerBase;
use VersionPress\Tests\SynchronizerTests\Utils\EntityUtils;
use VersionPress\Tests\Utils\DBAsserter;
use VersionPress\Utils\AbsoluteUrlReplacer;
use VersionPress\Utils\WordPressMissingFunctions;

class PostsSynchronizerTest extends SynchronizerTestCase
{
    /** @var DirectoryStorage */
    private $storage;
    /** @var DirectoryStorage */
    private $userStorage;
    /** @var DirectoryStorage */
    private $termStorage;
    /** @var DirectoryStorage */
    private $termTaxonomyStorage;

    /** @var SynchronizerBase */
    private $synchronizer;
    /** @var SynchronizerBase */
    private $usersSynchronizer;
    /** @var SynchronizerBase */
    private $termsSynchronizer;
    /** @var SynchronizerBase */
    private $termTaxonomiesSynchronizer;

    private static $authorVpId;
    private static $vpId;
    private static $categoryVpId;
    private static $categoryTaxonomyVpId;

    protected function setUp()
    {
        parent::setUp();
        $this->storage = self::$storageFactory->getStorage('post');
        $this->userStorage = self::$storageFactory->getStorage('user');
        $this->termStorage = self::$storageFactory->getStorage('term');
        $this->termTaxonomyStorage = self::$storageFactory->getStorage('term_taxonomy');

        $this->synchronizer = new SynchronizerBase(
            $this->storage,
            self::$database,
            self::$schemaInfo->getEntityInfo('post'),
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
        $this->termsSynchronizer = new SynchronizerBase(
            $this->termStorage,
            self::$database,
            self::$schemaInfo->getEntityInfo('term'),
            self::$schemaInfo,
            self::$vpidRepository,
            self::$urlReplacer,
            self::$shortcodesReplacer
        );
        $this->termTaxonomiesSynchronizer = new SynchronizerBase(
            $this->termTaxonomyStorage,
            self::$database,
            self::$schemaInfo->getEntityInfo('term_taxonomy'),
            self::$schemaInfo,
            self::$vpidRepository,
            self::$urlReplacer,
            self::$shortcodesReplacer
        );
    }

    /**
     * @test
     * @testdox Synchronizer adds new post to the database
     */
    public function synchronizerAddsNewPostToDatabase()
    {
        $this->createPost();
        $this->usersSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);

        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer updates changed post in the database
     */
    public function synchronizerUpdatesChangedPostInDatabase()
    {
        $this->editPost();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer replaces absolute URLs
     */
    public function synchronizerReplacesAbsoluteUrls()
    {
        $this->editPost('post_content', AbsoluteUrlReplacer::PLACEHOLDER);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer removes deleted post from the database
     */
    public function synchronizerRemovesDeletedPostFromDatabase()
    {
        $this->deletePost();

        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->usersSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer adds new post to the database (selective synchronization)
     */
    public function synchronizerAddsNewPostToDatabase_selective()
    {
        $entitiesToSynchronize = $this->createPost();
        $this->usersSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);

        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer updates changed post in the database (selective synchronization)
     */
    public function synchronizerUpdatesChangedPostInDatabase_selective()
    {
        $entitiesToSynchronize = $this->editPost();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer removes deleted post from the database (selective synchronization)
     */
    public function synchronizerRemovesDeletedPostFromDatabase_selective()
    {
        $entitiesToSynchronize = $this->deletePost();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        $this->usersSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer synchronizes term relationships (changes category)
     */
    public function synchronizerSynchronizesTermRelationships_changeCategory()
    {
        $this->createPost();
        $this->changeCategory();
        $this->usersSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->termsSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->termTaxonomiesSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer synchronizes term relationships (removes category)
     */
    public function synchronizerSynchronizesTermRelationships_removeCategory()
    {
        $this->removeCategory();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->termTaxonomiesSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->termsSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->usersSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer synchronizes term relationships (changes category, selective synchronization)
     */
    public function synchronizerSynchronizesTermRelationships_changeCategory_selective()
    {
        $entitiesToSynchronize = $this->createPost();
        $entitiesToSynchronize = array_merge($this->changeCategory(), $entitiesToSynchronize);
        $this->usersSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        $this->termsSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->termTaxonomiesSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer synchronizes term relationships (removes category, selective synchronization)
     */
    public function synchronizerSynchronizesTermRelationships_removeCategory_selective()
    {
        $entitiesToSynchronize = $this->removeCategory();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        $this->termTaxonomiesSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->termsSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->usersSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer restores shortcodes
     */
    public function synchronizerRestoresShortcodes()
    {
        $this->createPostWithShortcode();
        $this->usersSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);

        $synchronizationTasks = [Synchronizer::SYNCHRONIZE_EVERYTHING];
        while (count($synchronizationTasks)) {
            $task = array_shift($synchronizationTasks);
            $remainingTasks = $this->synchronizer->synchronize($task);
            $synchronizationTasks = array_merge($synchronizationTasks, $remainingTasks);
        }

        DBAsserter::assertFilesEqualDatabase();

        $this->deletePost();

        $this->synchronizer = new SynchronizerBase(
            $this->storage,
            self::$database,
            self::$schemaInfo->getEntityInfo('post'),
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
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->usersSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    private function createPost()
    {
        $author = EntityUtils::prepareUser();
        self::$authorVpId = $author['vp_id'];
        $this->userStorage->save($author);
        $post = EntityUtils::preparePost(null, self::$authorVpId);
        self::$vpId = $post['vp_id'];
        $this->storage->save($post);

        return [
            ['vp_id' => self::$authorVpId, 'parent' => self::$authorVpId],
            ['vp_id' => self::$vpId, 'parent' => self::$vpId],
        ];
    }

    private function editPost($key = 'post_status', $value = 'trash')
    {
        $this->storage->save(EntityUtils::preparePost(self::$vpId, null, [$key => $value]));
        return [
            ['vp_id' => self::$vpId, 'parent' => self::$vpId],
        ];
    }

    private function deletePost()
    {
        $this->storage->delete(EntityUtils::preparePost(self::$vpId));
        $this->userStorage->delete(EntityUtils::prepareUser(self::$authorVpId));

        return [
            ['vp_id' => self::$authorVpId, 'parent' => self::$authorVpId],
            ['vp_id' => self::$vpId, 'parent' => self::$vpId],
        ];
    }

    private function changeCategory()
    {
        $categoryTerm = EntityUtils::prepareTerm(null, 'Some category', 'some-category');
        self::$categoryVpId = $categoryTerm['vp_id'];
        $categoryTaxonomy = EntityUtils::prepareTermTaxonomy(null, self::$categoryVpId, 'category', 'Some description');
        self::$categoryTaxonomyVpId = $categoryTaxonomy['vp_id'];

        $this->termStorage->save($categoryTerm);
        $this->termTaxonomyStorage->save($categoryTaxonomy);

        $this->storage->save(EntityUtils::preparePost(
            self::$vpId,
            null,
            ['vp_term_taxonomy' => [self::$categoryTaxonomyVpId]]
        ));

        return [
            ['vp_id' => self::$categoryVpId, 'parent' => self::$categoryVpId],
            ['vp_id' => self::$categoryTaxonomyVpId, 'parent' => self::$categoryVpId],
            ['vp_id' => self::$vpId, 'parent' => self::$vpId],
        ];
    }

    private function removeCategory()
    {
        $this->storage->delete(EntityUtils::preparePost(self::$vpId));
        $this->termTaxonomyStorage->delete(
            EntityUtils::prepareTermTaxonomy(self::$categoryTaxonomyVpId, self::$categoryVpId)
        );
        $this->termStorage->delete(EntityUtils::prepareTerm(self::$categoryVpId));
        $this->userStorage->delete(EntityUtils::prepareUser(self::$authorVpId));

        return [
            ['vp_id' => self::$vpId, 'parent' => self::$vpId],
            ['vp_id' => self::$categoryTaxonomyVpId, 'parent' => self::$categoryVpId],
            ['vp_id' => self::$categoryVpId, 'parent' => self::$categoryVpId],
            ['vp_id' => self::$authorVpId, 'parent' => self::$authorVpId],
        ];
    }

    private function createPostWithShortcode()
    {
        $author = EntityUtils::prepareUser();
        self::$authorVpId = $author['vp_id'];
        $this->userStorage->save($author);

        $post = EntityUtils::preparePost(null, self::$authorVpId);
        self::$vpId = $post['vp_id'];
        $post['post_content'] = WordPressMissingFunctions::renderShortcode('gallery', ['id' => self::$vpId]);
        $this->storage->save($post);

        return [
            ['vp_id' => self::$authorVpId, 'parent' => self::$authorVpId],
            ['vp_id' => self::$vpId, 'parent' => self::$vpId],
        ];
    }
}
