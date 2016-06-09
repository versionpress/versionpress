<?php

namespace VersionPress\Tests\StorageTests;

use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Storages\CommentStorage;
use VersionPress\Storages\DirectoryStorage;
use VersionPress\Storages\MetaEntityStorage;
use VersionPress\Storages\OptionStorage;
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
            'comment' => CommentStorage::class,
            'option' => OptionStorage::class,
            'term' => DirectoryStorage::class,
            'termmeta' => MetaEntityStorage::class,
            'term_taxonomy' => DirectoryStorage::class,
            'user' => DirectoryStorage::class,
            'usermeta' => MetaEntityStorage::class,
            'postmeta' => MetaEntityStorage::class,
        ];

        /** @var \wpdb $wpdbStub */
        $wpdbStub = $this->getMockBuilder('\wpdb')->disableOriginalConstructor()->getMock();

        $database = new Database($wpdbStub);

        $factory = new StorageFactory(
            __DIR__ . '/vpdb',
            new DbSchemaInfo(
                __DIR__ . '/../../src/Database/wordpress-schema.yml',
                'wp_',
                PHP_INT_MAX
            ),
            $database,
            []
        );
        foreach ($storages as $entityName => $expectedClass) {
            $this->assertInstanceOf($expectedClass, $factory->getStorage($entityName));
        }

    }
}
