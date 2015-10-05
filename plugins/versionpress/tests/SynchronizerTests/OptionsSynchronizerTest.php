<?php

namespace VersionPress\Tests\SynchronizerTests;

use VersionPress\Storages\OptionStorage;
use VersionPress\Synchronizers\OptionsSynchronizer;
use VersionPress\Synchronizers\Synchronizer;
use VersionPress\Tests\SynchronizerTests\Utils\EntityUtils;
use VersionPress\Tests\Utils\DBAsserter;
use VersionPress\Tests\Utils\TestConfig;
use VersionPress\Utils\AbsoluteUrlReplacer;

class OptionsSynchronizerTest extends SynchronizerTestCase {

    /** @var OptionStorage */
    private $storage;
    /** @var OptionsSynchronizer */
    private $synchronizer;

    private $entitiesForSelectiveSynchronization = array(array('vp_id' => 'foo', 'parent' => null));

    protected function setUp() {
        parent::setUp();
        $this->storage = self::$storageFactory->getStorage('option');
        $this->synchronizer = new OptionsSynchronizer($this->storage, self::$wpdb, self::$schemaInfo, self::$urlReplacer);
    }

    /**
     * @test
     * @testdox Synchronizer adds new option to the database
     */
    public function synchronizerAddsNewOptionToDatabase() {
        $this->storage->save(EntityUtils::prepareOption('foo', 'bar'));
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer updates changed option in the database
     */
    public function synchronizerUpdatesChangedOptionInDatabase() {
        $this->storage->save(EntityUtils::prepareOption('foo', 'another value'));
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer replaces URLs
     */
    public function synchronizerReplacesUrls() {
        $this->storage->save(EntityUtils::prepareOption('foo', AbsoluteUrlReplacer::PLACEHOLDER));
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer removes deleted option from the database
     */
    public function synchronizerRemovesDeletedOptionFromDatabase() {
        $this->storage->delete(EntityUtils::prepareOption('foo', 'bar'));
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer adds new option to the database
     */
    public function synchronizerAddsNewOptionToDatabase_selective() {
        $this->storage->save(EntityUtils::prepareOption('foo', 'bar'));
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $this->entitiesForSelectiveSynchronization);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer updates changed option in the database
     */
    public function synchronizerUpdatesChangedOptionInDatabase_selective() {
        $this->storage->save(EntityUtils::prepareOption('foo', 'another value'));
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $this->entitiesForSelectiveSynchronization);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer removes deleted option from the database
     */
    public function synchronizerRemovesDeletedOptionFromDatabase_selective() {
        $this->storage->delete(EntityUtils::prepareOption('foo', 'bar'));
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $this->entitiesForSelectiveSynchronization);
        DBAsserter::assertFilesEqualDatabase();
    }
}