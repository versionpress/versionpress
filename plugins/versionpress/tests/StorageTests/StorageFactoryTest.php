<?php

namespace VersionPress\Tests\StorageTests;

use VersionPress\Actions\ActionsInfo;
use VersionPress\ChangeInfos\ChangeInfoFactory;
use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Storages\DirectoryStorage;
use VersionPress\Storages\MetaEntityStorage;
use VersionPress\Storages\StorageFactory;

class StorageFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @testdox Factory creates right storages
     */
    public function factoryCreatesRightStorages()
    {
        $storages = [
            'post' => DirectoryStorage::class,
            'comment' => DirectoryStorage::class,
            'option' => DirectoryStorage::class,
            'term' => DirectoryStorage::class,
            'termmeta' => MetaEntityStorage::class,
            'term_taxonomy' => DirectoryStorage::class,
            'user' => DirectoryStorage::class,
            'usermeta' => MetaEntityStorage::class,
            'postmeta' => MetaEntityStorage::class,
        ];

        /** @var \wpdb $wpdbStub */

        $wpdbStub = new \stdClass();
        $wpdbStub->prefix = 'prefix_';

        $database = new Database($wpdbStub);
        $changeInfoFactory = $this->getMockBuilder(ChangeInfoFactory::class)->disableOriginalConstructor()->getMock();

        $factory = new StorageFactory(
            __DIR__ . '/vpdb',
            new DbSchemaInfo(
                [__DIR__ . '/../../.versionpress/schema.yml'],
                'wp_',
                PHP_INT_MAX
            ),
            $database,
            [],
            $changeInfoFactory
        );

        foreach ($storages as $entityName => $expectedClass) {
            $this->assertInstanceOf($expectedClass, $factory->getStorage($entityName));
        }
    }
}
