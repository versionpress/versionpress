<?php

namespace VersionPress\Tests\SynchronizerTests;

use VersionPress\Storages\TermsStorage;
use VersionPress\Storages\TermTaxonomyStorage;
use VersionPress\Synchronizers\Synchronizer;
use VersionPress\Synchronizers\TermsSynchronizer;
use VersionPress\Synchronizers\TermTaxonomySynchronizer;
use VersionPress\Tests\SynchronizerTests\Utils\EntityUtils;
use VersionPress\Tests\Utils\DBAsserter;

class TermTaxonomySynchronizerTest extends SynchronizerTestCase {
    /** @var TermTaxonomyStorage */
    private $storage;
    /** @var TermsStorage */
    private $termStorage;
    /** @var TermTaxonomySynchronizer */
    private $synchronizer;
    /** @var TermsSynchronizer */
    private $termSynchronizer;
    private static $vpId;
    private static $termVpId;

    protected function setUp() {
        parent::setUp();
        $this->storage = self::$storageFactory->getStorage('term_taxonomy');
        $this->termStorage = self::$storageFactory->getStorage('term');
        $this->synchronizer = new TermTaxonomySynchronizer($this->storage, self::$wpdb, self::$schemaInfo);
        $this->termSynchronizer = new TermsSynchronizer($this->termStorage, self::$wpdb, self::$schemaInfo);
    }

    /**
     * @test
     * @testdox Synchronizer adds new term taxonomy to the database
     */
    public function synchronizerAddsNewTermTaxonomyToDatabase() {
        $this->createTermTaxonomy();
        $this->termSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer updates changed term taxonomy in the database
     */
    public function synchronizerUpdatesChangedTermTaxonomyInDatabase() {
        $this->editTermTaxonomy();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer removes deleted term taxonomy from the database
     */
    public function synchronizerRemovesDeletedTermTaxonomyFromDatabase() {
        $this->deleteTermTaxonomy();
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->termSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Synchronizer adds two term taxonomies with same taxonomy
     */
    public function synchronizerAddsTwoTermTaxonomiesWithTheSameTaxonomy() {
        $term1 = EntityUtils::prepareTerm(null, 'Some term', 'some-term');
        $term2 = EntityUtils::prepareTerm(null, 'Other term', 'other-term');
        $this->termStorage->save($term1);
        $this->termStorage->save($term2);

        $termTaxonomy1 = EntityUtils::prepareTermTaxonomy(null, $term1['vp_id'], 'category', 'Some description');
        $termTaxonomy2 = EntityUtils::prepareTermTaxonomy(null, $term2['vp_id'], 'category', 'Other description');
        $this->storage->save($termTaxonomy1);
        $this->storage->save($termTaxonomy2);

        $this->termSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $this->synchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        DBAsserter::assertFilesEqualDatabase();

        // cleanup
        $this->storage->delete($termTaxonomy1);
        $this->storage->delete($termTaxonomy2);
        $this->termStorage->delete($term1);
        $this->termStorage->delete($term2);
        // We need new instances because of caching in SynchronizerBase::maybeInit
        $termSynchronizer = new TermsSynchronizer($this->termStorage, self::$wpdb, self::$schemaInfo);
        $termTaxonomySynchronizer = new TermTaxonomySynchronizer($this->storage, self::$wpdb, self::$schemaInfo);
        $termTaxonomySynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
        $termSynchronizer->synchronize(Synchronizer::SYNCHRONIZE_EVERYTHING);
    }

    private function createTermTaxonomy() {
        $term = EntityUtils::prepareTerm(null, 'Some term', 'some-term');
        self::$termVpId = $term['vp_id'];
        $this->termStorage->save($term);

        $termTaxonomy = EntityUtils::prepareTermTaxonomy(null, self::$termVpId, 'category', 'Some description');
        self::$vpId = $termTaxonomy['vp_id'];
        $this->storage->save($termTaxonomy);

        return array(
            array('vp_id' => self::$termVpId, 'parent' => self::$termVpId),
            array('vp_id' => self::$vpId, 'parent' => self::$termVpId),
        );
    }

    private function editTermTaxonomy() {
        $this->storage->save(EntityUtils::prepareTermTaxonomy(self::$vpId, self::$termVpId, 'category', 'Another description'));
        return array(array('vp_id' => self::$vpId, 'parent' => self::$termVpId));
    }

    private function deleteTermTaxonomy() {
        $this->storage->delete(EntityUtils::prepareTermTaxonomy(self::$vpId));
        $this->termStorage->delete(EntityUtils::prepareTerm(self::$termVpId));
        return array(
            array('vp_id' => self::$termVpId, 'parent' => self::$termVpId),
            array('vp_id' => self::$vpId, 'parent' => self::$termVpId),
        );
    }
}