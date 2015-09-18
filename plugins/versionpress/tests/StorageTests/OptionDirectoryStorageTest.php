<?php

namespace VersionPress\Tests\StorageTests;

use VersionPress\Database\EntityInfo;
use VersionPress\Storages\OptionDirectoryStorage;
use VersionPress\Storages\OptionsStorage;
use VersionPress\Tests\Utils\ArrayAsserter;
use VersionPress\Utils\FileSystem;

class OptionDirectoryStorageTest extends \PHPUnit_Framework_TestCase {
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
        ArrayAsserter::assertSimilar($this->testingOption, $loadedOption);
    }

    /**
     * @test
     */
    public function loadAllReturnsOnlyOriginalEntities() {
        $this->storage->save($this->testingOption);
        $loadedOptions = $this->storage->loadAll();
        $this->assertTrue(count($loadedOptions) === 1);
        ArrayAsserter::assertSimilar($this->testingOption, reset($loadedOptions));
    }

    /**
     * @test
     */
    public function savedOptionDoesNotContainOptionNameKey() {
        $this->storage->save($this->testingOption);
        $fileName = $this->storage->getEntityFilename($this->testingOption['option_name']);
        $content = file_get_contents($fileName);
        $this->assertFalse(strpos($content, 'option_name'), 'Option contains an option_name key');
    }

    /**
     * @test
     */
    public function storageSupportsOptionsWithDotsInName() {
        $testingOption = array(
            "option_name" => "some option with . in name",
            "option_value" => "some value",
            "autoload" => "yes",
        );

        $this->storage->save($testingOption);
        $loadedOption = $this->storage->loadEntity($testingOption['option_name']);
        ArrayAsserter::assertSimilar($testingOption, $loadedOption);
    }

    /**
     * Test covers part of for WP-428
     *
     * @test
     */
    public function vpidShouldBeReplaceableWithZero() {
        $testingOption = array(
            'option_name' => 'some_option',
            'option_value' => 'FE00B4B4D5FE4FD4ACAFF9D11A78F44E',
            'autoload' => 'yes'
        );

        $updatedOption = array(
            'option_name' => 'some_option',
            'option_value' => 0
        );

        $expectedOption = array_merge($testingOption, $updatedOption);

        $this->storage->save($testingOption);
        $this->storage->save($updatedOption);

        $loadedOption = $this->storage->loadEntity($testingOption['option_name']);
        ArrayAsserter::assertSimilar($expectedOption, $loadedOption);
    }

    protected function setUp() {
        parent::setUp();
        $entityInfo = new EntityInfo(array(
            'option' => array(
                'table' => 'options',
                'vpid' => 'option_name',
            )
        ));

        $this->storage = new OptionDirectoryStorage(__DIR__ . '/options', $entityInfo);
    }

    protected function tearDown() {
        parent::tearDown();
        FileSystem::remove(__DIR__ . '/options');
    }
}
