<?php

namespace VersionPress\Tests\SynchronizerTests;

use VersionPress\Storages\OptionsStorage;
use VersionPress\Synchronizers\OptionsSynchronizer;
use VersionPress\Synchronizers\Synchronizer;
use VersionPress\Tests\SynchronizerTests\Utils\EntityUtils;
use VersionPress\Tests\Utils\DBAsserter;
use VersionPress\Tests\Utils\TestConfig;

class OptionsSynchronizerTest extends SynchronizerTestCase {

    /** @var OptionsStorage */
    private $storage;
    /** @var OptionsSynchronizer */
    private $synchronizer;

    protected function setUp() {
        parent::setUp();
        $this->storage = self::$storageFactory->getStorage('option');
        $this->synchronizer = new OptionsSynchronizer($this->storage, self::$wpdb, self::$schemaInfo);
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
     * @testdox Synchronizer removes deleted option from the database
     */
    public function synchronizerRemovesDeletedOptionFromDatabase() {
        $this->storage->delete(EntityUtils::prepareOption('foo', 'bar'));
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }
}