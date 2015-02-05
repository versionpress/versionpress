<?php

namespace VersionPress\Tests\StorageTests;

use VersionPress\Database\EntityInfo;
use VersionPress\Storages\TermsStorage;
use VersionPress\Utils\FileSystem;

class TermsStorageTest extends \PHPUnit_Framework_TestCase {
    /** @var TermsStorage */
    private $storage;

    private $testingTerm = array(
        "name" => "Uncategorized",
        "slug" => "uncategorized",
        "term_group" => 0,
        "vp_id" => "566D438B716C404D8CC384AE8F86A974",
    );

    /**
     * @test
     */
    public function savedTermEqualsLoadedTerm() {
        $this->storage->save($this->testingTerm);
        $loadedTerm = $this->storage->loadEntity($this->testingTerm['vp_id']);
        $this->assertTrue($this->testingTerm == $loadedTerm);
    }

    protected function setUp() {
        parent::setUp();
        $entityInfo = new EntityInfo(array(
            'term' => array(
                'table' => 'terms',
                'id' => 'term_id',
            )
        ));

        $this->storage = new TermsStorage(__DIR__ . '/terms.ini', $entityInfo);
    }

    protected function tearDown() {
        parent::tearDown();
        FileSystem::remove(__DIR__ . '/terms.ini');
    }
}
