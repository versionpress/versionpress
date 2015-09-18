<?php
namespace VersionPress\Tests\StorageTests;

use VersionPress\Database\EntityInfo;
use VersionPress\Storages\OptionDirectoryStorage;
use VersionPress\Storages\OptionsStorage;
use VersionPress\Tests\Utils\ArrayAsserter;
use VersionPress\Utils\FileSystem;

class OptionStorageEqualityTest extends \PHPUnit_Framework_TestCase {

    const TABLE_PREFIX = 'prefix_';

    /** @var OptionsStorage */
    private $singleFileStorage;
    /** @var OptionDirectoryStorage */
    private $directoryStorage;

    /**
     * @test
     * @dataProvider optionProvider
     * @param $option
     */
    public function storagesWorkEqually($option) {
        $this->singleFileStorage->save($option);
        $this->directoryStorage->save($option);

        $f1 = $this->singleFileStorage->getEntityFilename($option['option_name']);
        $f2 = $this->directoryStorage->getEntityFilename($option['option_name']);

        ArrayAsserter::assertSimilar($this->singleFileStorage->loadAll(), $this->directoryStorage->loadAll());
        $this->assertSame(@file_get_contents($f1), @file_get_contents($f2)); // intentionally @ - the files don't exist if the option was not saved
    }

    public function optionProvider() {
        $simpleOption = array('option_name' => 'foo', 'option_value' => 'bar', 'autoload' => 'yes');
        $optionWithPrefix = array('option_name' => self::TABLE_PREFIX . 'foo', 'option_value' => 'bar', 'autoload' => 'yes');

        $blacklistedOptions = array_map(function ($optionName) { return array('option_name' => $optionName, 'option_value' => 'bar', 'autoload' => 'yes'); }, OptionDirectoryStorage::$optionsBlacklist);
        $transientOption = array('option_name' => '_foo', 'option_value' => 'bar', 'autoload' => 'yes');

        $allOptions = array_merge(array($simpleOption, $optionWithPrefix, $transientOption), $blacklistedOptions);

        return array_map(function ($option) { return array($option); }, $allOptions);
    }

    protected function setUp() {
        $entityInfo = new EntityInfo(array(
            'option' => array(
                'table' => 'options',
                'vpid' => 'option_name',
            )
        ));

        $this->singleFileStorage = new OptionsStorage(__DIR__ . '/options.ini', $entityInfo, self::TABLE_PREFIX);
        $this->directoryStorage = new OptionDirectoryStorage(__DIR__ . '/options', $entityInfo, self::TABLE_PREFIX);
    }

    public function tearDown() {
        FileSystem::remove(__DIR__ . '/options.ini');
        FileSystem::remove(__DIR__ . '/options');
    }
}