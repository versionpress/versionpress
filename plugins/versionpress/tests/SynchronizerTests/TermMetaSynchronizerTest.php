<?php

namespace VersionPress\Tests\SynchronizerTests;

use VersionPress\Storages\TermMetaStorage;
use VersionPress\Storages\TermStorage;
use VersionPress\Synchronizers\Synchronizer;
use VersionPress\Synchronizers\TermMetaSynchronizer;
use VersionPress\Synchronizers\TermsSynchronizer;
use VersionPress\Tests\SynchronizerTests\Utils\EntityUtils;
use VersionPress\Tests\Utils\DBAsserter;
use VersionPress\Utils\AbsoluteUrlReplacer;

class TermMetaSynchronizerTest extends SynchronizerTestCase
{
    /** @var TermMetaStorage */
    private $storage;
    /** @var TermStorage */
    private $termStorage;
    /** @var TermMetaSynchronizer */
    private $synchronizer;
    /** @var TermsSynchronizer */
    private $termsSynchronizer;
    private static $vpId;
    private static $termVpId;

    protected function setUp()
    {
        if (!in_array('termmeta', self::$schemaInfo->getAllEntityNames())) {
            throw new \PHPUnit_Framework_SkippedTestError("Termmeta are not supported in this version of WP");
        }

        parent::setUp();
        $this->storage = self::$storageFactory->getStorage('termmeta');
        $this->termStorage = self::$storageFactory->getStorage('term');
        $this->synchronizer = new TermMetaSynchronizer(
            $this->storage,
            self::$database,
            self::$schemaInfo->getEntityInfo('termmeta'),
            self::$schemaInfo,
            self::$vpidRepository,
            self::$urlReplacer,
            self::$shortcodesReplacer
        );
        $this->termsSynchronizer = new TermsSynchronizer(
            $this->termStorage,
            self::$database,
            self::$schemaInfo->getEntityInfo('term'),
            self::$schemaInfo,
            self::$vpidRepository,
            self::$urlReplacer,
            self::$shortcodesReplacer
        );
    }

    /**
     * @test
     * @testdox Synchronizer adds new termmeta to the database
     */
    public function synchronizerAddsNewTermMetaToDatabase()
    {
        $this->createTermMeta();
        $this->termsSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer updates changed termmeta in the database
     */
    public function synchronizerUpdatesChangedTermMetaInDatabase()
    {
        $this->editTermMeta();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer replaces absolute URLs
     */
    public function synchronizerReplacesAbsoluteUrls()
    {
        $this->editTermMeta('some-meta', AbsoluteUrlReplacer::PLACEHOLDER);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer removes deleted termmeta from the database
     */
    public function synchronizerRemovesDeletedTermMetaFromDatabase()
    {
        $this->deleteTermMeta();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->termsSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer adds new termmeta to the database (selective synchronization)
     */
    public function synchronizerAddsNewTermMetaToDatabase_selective()
    {
        $entitiesToSynchronize = $this->createTermMeta();
        $this->termsSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer updates changed termmeta in the database (selective synchronization)
     */
    public function synchronizerUpdatesChangedTermMetaInDatabase_selective()
    {
        $entitiesToSynchronize = $this->editTermMeta();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer removes deleted termmeta from the database (selective synchronization)
     */
    public function synchronizerRemovesDeletedTermMetaFromDatabase_selective()
    {
        $entitiesToSynchronize = $this->deleteTermMeta();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        $this->termsSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING, $entitiesToSynchronize);
        DBAsserter::assertFilesEqualDatabase();
    }

    private function createTermMeta()
    {
        $term = EntityUtils::prepareTerm();
        self::$termVpId = $term['vp_id'];
        $this->termStorage->save($term);
        $termmeta = EntityUtils::prepareTermMeta(null, self::$termVpId, 'some-meta', 'some value');
        $this->storage->save($termmeta);

        self::$vpId = $termmeta['vp_id'];
        return [
            ['vp_id' => self::$vpId, 'parent' => self::$termVpId],
            ['vp_id' => self::$termVpId, 'parent' => self::$termVpId],
        ];
    }

    private function editTermMeta($key = 'some-meta', $value = 'another value')
    {
        $this->storage->save(EntityUtils::prepareTermMeta(self::$vpId, self::$termVpId, $key, $value));
        return [
            ['vp_id' => self::$vpId, 'parent' => self::$termVpId],
        ];
    }

    private function deleteTermMeta()
    {
        $this->storage->delete(EntityUtils::prepareTermMeta(self::$vpId, self::$termVpId));
        $this->termStorage->delete(EntityUtils::prepareTerm(self::$termVpId));

        return [
            ['vp_id' => self::$vpId, 'parent' => self::$termVpId],
            ['vp_id' => self::$termVpId, 'parent' => self::$termVpId],
        ];
    }
}
