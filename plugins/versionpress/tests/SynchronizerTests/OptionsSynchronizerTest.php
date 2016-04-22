<?php

namespace VersionPress\Tests\SynchronizerTests;

use VersionPress\Storages\OptionStorage;
use VersionPress\Storages\PostStorage;
use VersionPress\Synchronizers\OptionsSynchronizer;
use VersionPress\Synchronizers\PostsSynchronizer;
use VersionPress\Synchronizers\Synchronizer;
use VersionPress\Tests\SynchronizerTests\Utils\EntityUtils;
use VersionPress\Tests\Utils\DBAsserter;
use VersionPress\Utils\AbsoluteUrlReplacer;

class OptionsSynchronizerTest extends SynchronizerTestCase
{

    /** @var OptionStorage */
    private $storage;
    /** @var PostStorage */
    private $postStorage;
    /** @var OptionsSynchronizer */
    private $synchronizer;
    /** @var PostsSynchronizer */
    private $postsSynchronizer;

    private $entitiesForSelectiveSynchronization = [['vp_id' => 'foo', 'parent' => null]];

    protected function setUp()
    {
        parent::setUp();
        $this->storage = self::$storageFactory->getStorage('option');
        $this->postStorage = self::$storageFactory->getStorage('post');
        $this->synchronizer = new OptionsSynchronizer(
            $this->storage,
            self::$database,
            self::$schemaInfo->getEntityInfo('option'),
            self::$schemaInfo,
            self::$vpidRepository,
            self::$urlReplacer,
            self::$shortcodesReplacer
        );
        $this->postsSynchronizer = new PostsSynchronizer(
            $this->postStorage,
            self::$database,
            self::$schemaInfo->getEntityInfo('post'),
            self::$schemaInfo,
            self::$vpidRepository,
            self::$urlReplacer,
            self::$shortcodesReplacer
        );
    }

    /**
     * @test
     * @testdox Synchronizer adds new option to the database
     */
    public function synchronizerAddsNewOptionToDatabase()
    {
        $this->storage->save(EntityUtils::prepareOption('foo', 'bar'));
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer updates changed option in the database
     */
    public function synchronizerUpdatesChangedOptionInDatabase()
    {
        $this->storage->save(EntityUtils::prepareOption('foo', 'another value'));
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer replaces URLs
     */
    public function synchronizerReplacesUrls()
    {
        $this->storage->save(EntityUtils::prepareOption('foo', AbsoluteUrlReplacer::PLACEHOLDER));
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer removes deleted option from the database
     */
    public function synchronizerRemovesDeletedOptionFromDatabase()
    {
        $this->storage->delete(EntityUtils::prepareOption('foo', 'bar'));
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer adds new option to the database
     */
    public function synchronizerAddsNewOptionToDatabase_selective()
    {
        $this->storage->save(EntityUtils::prepareOption('foo', 'bar'));
        $this->synchronizer->synchronize(
            Synchronizer::SYNCHRONIZE_EVERYTHING,
            $this->entitiesForSelectiveSynchronization
        );
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer updates changed option in the database
     */
    public function synchronizerUpdatesChangedOptionInDatabase_selective()
    {
        $this->storage->save(EntityUtils::prepareOption('foo', 'another value'));
        $this->synchronizer->synchronize(
            Synchronizer::SYNCHRONIZE_EVERYTHING,
            $this->entitiesForSelectiveSynchronization
        );
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer removes deleted option from the database
     */
    public function synchronizerRemovesDeletedOptionFromDatabase_selective()
    {
        $this->storage->delete(EntityUtils::prepareOption('foo', 'bar'));
        $this->synchronizer->synchronize(
            Synchronizer::SYNCHRONIZE_EVERYTHING,
            $this->entitiesForSelectiveSynchronization
        );
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer replaces value references
     */
    public function synchronizerReplacesValueReferences()
    {
        $post = EntityUtils::preparePost();
        $optionToSynchronize = [['vp_id' => 'site_icon', 'parent' => null]];
        $postToSynchronize = [['vp_id' => $post['vp_id'], 'parent' => null]];

        $previousSiteIcon = $this->storage->exists('site_icon') ? $this->storage->loadEntity('site_icon') : '';
        $this->postStorage->save($post);
        $option = EntityUtils::prepareOption('site_icon', $post['vp_id']);
        $this->storage->save($option);

        $this->postsSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $postToSynchronize);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $optionToSynchronize);
        DBAsserter::assertFilesEqualDatabase();

        // cleanup
        if ($previousSiteIcon) {
            if (!isset($previousSiteIcon['option_value'])) {
                $previousSiteIcon['option_value'] = false;
            }

            $this->storage->save($previousSiteIcon);
        } else {
            $this->storage->delete($option);
        }

        $this->postStorage->delete($post);

        // We need new instances because of caching in SynchronizerBase::maybeInit
        $this->synchronizer = new OptionsSynchronizer(
            $this->storage,
            self::$database,
            self::$schemaInfo->getEntityInfo('option'),
            self::$schemaInfo,
            self::$vpidRepository,
            self::$urlReplacer,
            self::$shortcodesReplacer
        );
        $this->postsSynchronizer = new PostsSynchronizer(
            $this->postStorage,
            self::$database,
            self::$schemaInfo->getEntityInfo('post'),
            self::$schemaInfo,
            self::$vpidRepository,
            self::$urlReplacer,
            self::$shortcodesReplacer
        );
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $optionToSynchronize);
        $this->postsSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $postToSynchronize);
    }
}
