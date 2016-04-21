<?php

namespace VersionPress\Tests\StorageTests;

use VersionPress\Database\EntityInfo;
use VersionPress\Storages\TermStorage;
use VersionPress\Storages\TermTaxonomyStorage;
use VersionPress\Utils\FileSystem;

class TermTaxonomyStorageTest extends \PHPUnit_Framework_TestCase
{
    /** @var TermTaxonomyStorage */
    private $storage;

    private $testingTermTaxonomy = [
        "taxonomy" => "category",
        "description" => "",
        "vp_id" => "2AEF07792E494B31A15FCB392E9D37B5",
        "vp_term_id" => "566D438B716C404D8CC384AE8F86A974",
    ];

    /**
     * @test
     */
    public function savedTermTaxonomyEqualsLoadedTermTaxonomy()
    {
        $this->storage->save($this->testingTermTaxonomy);
        $loadedTermTaxonomy = $this->storage->loadEntity($this->testingTermTaxonomy['vp_id']);
        $this->assertEquals($this->testingTermTaxonomy, $loadedTermTaxonomy);
    }

    /**
     * @test
     */
    public function loadAllReturnsOnlyOriginalEntities()
    {
        $this->storage->save($this->testingTermTaxonomy);
        $loadedTermTaxonomies = $this->storage->loadAll();
        $this->assertTrue(count($loadedTermTaxonomies) === 1);
        $this->assertEquals($this->testingTermTaxonomy, reset($loadedTermTaxonomies));
    }

    /**
     * @test
     */
    public function storageDoesNotContainDeletedEntities()
    {
        $anotherTestingTermTaxonomy = $this->testingTermTaxonomy;
        $anotherTestingTermTaxonomy['taxonomy'] = 'tag';
        $anotherTestingTermTaxonomy['vp_id'] = 'CB6EBE2FD5D54BD9A5E1B54565A6E862';

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
    public function savedTaxonomyDoesNotContainVpIdKey()
    {
        $this->storage->save($this->testingTermTaxonomy);
        $fileName = $this->storage->getEntityFilename($this->testingTermTaxonomy['vp_id']);
        $content = file_get_contents($fileName);
        $this->assertFalse(strpos($content, 'vp_id'), 'Entity contains a vp_id key');
    }

    /**
     * @test
     */
    public function savedTaxonomyDoesNotContainCount()
    {
        $termTaxonomy = $this->testingTermTaxonomy;
        $termTaxonomy['count'] = 7;

        $this->storage->save($this->testingTermTaxonomy);
        $fileName = $this->storage->getEntityFilename($this->testingTermTaxonomy['vp_id']);
        $content = file_get_contents($fileName);
        $this->assertFalse(strpos($content, 'count'), 'Entity contains a count key');
    }

    protected function setUp()
    {
        parent::setUp();

        $termTaxonomyInfo = new EntityInfo([
            'term_taxonomy' => [
                'id' => 'term_taxonomy_id',
                'references' => [
                    'parent' => 'term',
                    'term_id' => 'term'
                ]
            ]
        ]);

        $termStorageMock = $this->getMockBuilder(TermStorage::class)
            ->disableOriginalConstructor()->getMock();
        $termStorageMock->expects($this->any())->method('loadEntity')->will($this->returnValue([
            "name" => "Uncategorized",
            "slug" => "uncategorized",
            "term_group" => 0,
            "vp_id" => "566D438B716C404D8CC384AE8F86A974",
        ]));

        /** @var TermStorage $termStorageMock */
        $this->storage = new TermTaxonomyStorage(__DIR__ . '/term_taxonomies', $termTaxonomyInfo, $termStorageMock);
    }

    protected function tearDown()
    {
        parent::tearDown();
        FileSystem::remove(__DIR__ . '/term_taxonomies');
    }
}
