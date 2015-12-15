<?php

namespace VersionPress\Tests\StorageTests;

use VersionPress\Database\DbSchemaInfo;
use VersionPress\Storages\StorageFactory;

class StorageFactoryTest extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     * @testdox Factory creates right storages
     */
    public function factoryCreatesRightStorages() {
        $storages = array(
            'post' => 'VersionPress\Storages\PostStorage',
            'comment' => 'VersionPress\Storages\CommentStorage',
            'option' => 'VersionPress\Storages\OptionStorage',
            'term' => 'VersionPress\Storages\TermStorage',
            'termmeta' => 'VersionPress\Storages\TermMetaStorage',
            'term_taxonomy' => 'VersionPress\Storages\TermTaxonomyStorage',
            'user' => 'VersionPress\Storages\UserStorage',
            'usermeta' => 'VersionPress\Storages\UserMetaStorage',
            'postmeta' => 'VersionPress\Storages\PostMetaStorage',
        );

        /** @var \wpdb $wpdbStub */
        $wpdbStub = $this->getMockBuilder('\wpdb')->disableOriginalConstructor()->getMock();

        $factory = new StorageFactory(__DIR__ . '/vpdb', new DbSchemaInfo(__DIR__ . '/../../src/Database/wordpress-schema.neon', 'wp_', PHP_INT_MAX), $wpdbStub, array());
        foreach ($storages as $entityName => $expectedClass) {
            $this->assertInstanceOf($expectedClass, $factory->getStorage($entityName));
        }

    }
}
