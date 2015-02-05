<?php

namespace VersionPress\Tests\StorageTests;

use VersionPress\Database\EntityInfo;
use VersionPress\Storages\PostMetaStorage;
use VersionPress\Storages\PostStorage;
use VersionPress\Storages\TermsStorage;
use VersionPress\Storages\TermTaxonomyStorage;
use VersionPress\Storages\UserMetaStorage;
use VersionPress\Storages\UserStorage;
use VersionPress\Utils\FileSystem;

class TermTaxonomyStorageTest extends \PHPUnit_Framework_TestCase {
    /** @var TermTaxonomyStorage */
    private $storage;
    /** @var TermsStorage */
    private $termStorage;

    private $testingTermTaxonomy = array(
        "term_taxonomy_id" => 1,
        "taxonomy" => "category",
        "description" => "",
        "vp_id" => "2AEF07792E494B31A15FCB392E9D37B5",
        "vp_term_id" => "566D438B716C404D8CC384AE8F86A974",
    );

    private $testingTerm = array(
        "term_id" => 1,
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
        $loadedTermTaxonomy = $this->storage->loadEntity($this->testingTermTaxonomy['vp_id']);
        $this->assertTrue($this->testingTermTaxonomy == $loadedTermTaxonomy);
    }

    /**
     * @test
     */
    public function loadAllReturnsOnlyOriginalEntities() {
        $this->termStorage->save($this->testingTerm);
        $this->storage->save($this->testingTermTaxonomy);
        $loadedTermTaxonomies = $this->storage->loadAll();
        $this->assertTrue(count($loadedTermTaxonomies) === 1);
        $this->assertTrue($this->testingTermTaxonomy == reset($loadedTermTaxonomies));
    }

    protected function setUp() {
        parent::setUp();
        $termInfo = new EntityInfo(array(
            'term' => array(
                'table' => 'termss',
                'id' => 'term_id',
            )
        ));

        $termTaxonomyInfo = array(
            'term_taxonomy' => array(
                'id' => 'term_taxonomy_id',
                'references' => array(
                    'parent' => 'term_taxonomy',
                    'term_id' => 'term'
                )
            )
        );

        $this->termStorage = new TermsStorage(__DIR__ . '/terms.ini', $termInfo);
        $this->storage = new TermTaxonomyStorage(__DIR__ . '/terms.ini', $termTaxonomyInfo);
    }

    protected function tearDown() {
        parent::tearDown();
        FileSystem::remove(__DIR__ . '/terms.ini');
    }
}
