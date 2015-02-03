<?php

namespace VersionPress\Tests\StorageTests;


use VersionPress\Database\DbSchemaInfo;
use VersionPress\Storages\StorageFactory;


class StorageFactoryTest extends \PHPUnit_Framework_TestCase {

    public function testBanan() {
        $factory = new StorageFactory(__DIR__ . '/vpdb', new DbSchemaInfo(__DIR__ . '/../../src/Database/wordpress-schema.neon', 'wp_'));
        $postMetaStorage = $factory->getStorage('postmeta');
        $this->assertInstanceOf('VersionPress\Storages\PostMetaStorage', $postMetaStorage);
    }
}

require_once('fakes.php');