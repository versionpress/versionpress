<?php

namespace VersionPress\Tests\StorageTests;

use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Storages\CommentStorage;
use VersionPress\Storages\OptionStorage;
use VersionPress\Storages\PostMetaStorage;
use VersionPress\Storages\PostStorage;
use VersionPress\Storages\StorageFactory;
use VersionPress\Storages\TermMetaStorage;
use VersionPress\Storages\TermStorage;
use VersionPress\Storages\TermTaxonomyStorage;
use VersionPress\Storages\UserMetaStorage;
use VersionPress\Storages\UserStorage;

class StorageFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @testdox Factory creates right storages
     */
    public function factoryCreatesRightStorages()
    {
        $storages = [
            'post' => PostStorage::class,
            'comment' => CommentStorage::class,
            'option' => OptionStorage::class,
            'term' => TermStorage::class,
            'termmeta' => TermMetaStorage::class,
            'term_taxonomy' => TermTaxonomyStorage::class,
            'user' => UserStorage::class,
            'usermeta' => UserMetaStorage::class,
            'postmeta' => PostMetaStorage::class,
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
