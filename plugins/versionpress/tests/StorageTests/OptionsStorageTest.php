<?php

namespace VersionPress\Tests\StorageTests;

use VersionPress\Database\EntityInfo;
use VersionPress\Storages\OptionsStorage;
use VersionPress\Utils\FileSystem;

class OptionsStorageTest extends \PHPUnit_Framework_TestCase {
    /** @var OptionsStorage */
    private $storage;

    private $testingOption = array(
        "option_name" => "blogdescription",
        "option_value" => "Just another WordPress site",
        "autoload" => "yes",
    );

    /**
     * @test
     */
    public function savedOptionEqualsLoadedOption() {
        $this->storage->save($this->testingOption);
        $loadedOption = $this->storage->loadEntity($this->testingOption['option_name']);
        $this->assertTrue($this->testingOption == $loadedOption);
    }

    /**
     * @test
     */
    public function loadAllReturnsOnlyOriginalEntities() {
        $this->storage->save($this->testingOption);
        $loadedOptions = $this->storage->loadAll();
        $this->assertTrue(count($loadedOptions) === 1);
        $this->assertTrue($this->testingOption == reset($loadedOptions));
    }

    protected function setUp() {
        parent::setUp();
        $entityInfo = new EntityInfo(array(
            'option' => array(
                'table' => 'options',
                'vpid' => 'option_name',
            )
        ));

        $this->storage = new OptionsStorage(__DIR__ . '/options.ini', $entityInfo);
    }

    protected function tearDown() {
        parent::tearDown();
        FileSystem::remove(__DIR__ . '/options.ini');
    }
}
