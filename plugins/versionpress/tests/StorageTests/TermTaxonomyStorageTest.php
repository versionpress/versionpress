<?php

namespace VersionPress\Tests\StorageTests;

use VersionPress\Database\EntityInfo;
use VersionPress\Storages\PostMetaStorage;
use VersionPress\Storages\PostStorage;
use VersionPress\Storages\TermStorage;
use VersionPress\Storages\TermTaxonomyStorage;
use VersionPress\Storages\UserMetaStorage;
use VersionPress\Storages\UserStorage;
use VersionPress\Utils\FileSystem;

class TermTaxonomyStorageTest extends \PHPUnit_Framework_TestCase {
    /** @var TermTaxonomyStorage */
    private $storage;
    /** @var TermStorage */
    private $termStorage;

    private $testingTermTaxonomy = array(
        "taxonomy" => "category",
        "description" => "",
        "vp_id" => "2AEF07792E494B31A15FCB392E9D37B5",
        "vp_term_id" => "566D438B716C404D8CC384AE8F86A974",
    );

    private $testingTerm = array(
        "name" => "Uncategorized",
        "slug" => "uncategorized",
        "term_group" => 0,
        "vp_id" => "566D438B716C404D8CC384AE8F86A974",
    );

    /**
     * @test
     */
    public function savedTermTaxonomyEqualsLoadedTermTaxonomy() {
        $this->termStorage->save($this->testingTerm);
        $this->storage->save($this->testingTermTaxonomy);
        $loadedTermTaxonomy = $this->storage->loadEntity($this->testingTermTaxonomy['vp_id'], $this->testingTerm['vp_id']);
        $this->assertEquals($this->testingTermTaxonomy, $loadedTermTaxonomy);
    }

    /**
     * @test
     */
    public function loadAllReturnsOnlyOriginalEntities() {
        $this->termStorage->save($this->testingTerm);
        $this->storage->save($this->testingTermTaxonomy);
        $loadedTermTaxonomies = $this->storage->loadAll();
        $this->assertTrue(count($loadedTermTaxonomies) === 1);
        $this->assertEquals($this->testingTermTaxonomy, reset($loadedTermTaxonomies));
    }

    /**
     * @test
     */
    public function storageDoesNotContainDeletedEntities() {
        $anotherTestingTermTaxonomy = $this->testingTermTaxonomy;
        $anotherTestingTermTaxonomy['taxonomy'] = 'tag';
        $anotherTestingTermTaxonomy['vp_id'] = 'CB6EBE2FD5D54BD9A5E1B54565A6E862';

        $this->termStorage->save($this->testingTerm);
        $this->storage->save($this->testingTermTaxonomy);
        $this->storage->save($anotherTestingTermTaxonomy);

        $loadedTermTaxonomies = $this->storage->loadAll();
        $this->assertTrue(count($loadedTermTaxonomies) === 2);

        $this->storage->delete($this->testingTermTaxonomy);

        $loadedTermTaxonomies = $this->storage->loadAll();
        $this->assertTrue(count($loadedTermTaxonomies) === 1);
        $this->assertEquals($anotherTestingTermTaxonomy, reset($loadedTermTaxonomies));
    }

    /**
     * @test
     */
    public function savedTaxonomyDoesNotContainVpIdKey() {
        $this->termStorage->save($this->testingTerm);
        $this->storage->save($this->testingTermTaxonomy);
        $fileName = $this->storage->getEntityFilename($this->testingTermTaxonomy['vp_id'], $this->testingTerm['vp_id']);
        $content = file_get_contents($fileName);
        $this->assertFalse(strpos($content, 'vp_id'), 'Entity contains a vp_id key');
    }

    protected function setUp() {
        parent::setUp();
        $termInfo = new EntityInfo(array(
            'term' => array(
                'table' => 'terms',
                'id' => 'term_id',
            )
        ));

        $termTaxonomyInfo = new EntityInfo(array(
            'term_taxonomy' => array(
                'id' => 'term_taxonomy_id',
                'references' => array(
                    'parent' => 'term_taxonomy',
                    'term_id' => 'term'
                )
            )
        ));

        $this->termStorage = new TermStorage(__DIR__ . '/terms', $termInfo);
        $this->storage = new TermTaxonomyStorage($this->termStorage, $termTaxonomyInfo);
    }

    protected function tearDown() {
        parent::tearDown();
        FileSystem::remove(__DIR__ . '/terms.ini');
    }
}
