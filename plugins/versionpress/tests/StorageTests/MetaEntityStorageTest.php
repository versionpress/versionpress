<?php

namespace VersionPress\Tests\StorageTests;

use VersionPress\Storages\DirectoryStorage;
use VersionPress\Storages\MetaEntityStorage;
use VersionPress\Utils\FileSystem;

class MetaEntityStorageTest extends StorageTestCase
{
    /** @var MetaEntityStorage */
    private $storage;
    /** @var DirectoryStorage */
    private $parentStorage;
    /** @var string */
    private $storageDir;

    private $testingMetaEntity = [
        'meta_key' => 'some-meta',
        'meta_value' => 'value',
        'vp_id' => "F11A5FF2219A3430E099B3838C42EBCA",
        'vp_parent_id' => "F0E1B6313B7A48E49A1B38DF382B350D",
    ];
    private $testingParentEntity = [
        'post_content' => "Welcome to WordPress.",
        'post_title' => "Hello world!",
        'post_status' => "publish",
        'post_type' => "post",
        'vp_id' => "F0E1B6313B7A48E49A1B38DF382B350D",
    ];

    /**
     * @test
     */
    public function savedMetaEntityEqualsLoadedMetaEntity()
    {
        $this->parentStorage->save($this->testingParentEntity);
        $this->storage->save($this->testingMetaEntity);
        $loadedMetaEntity = $this->storage->loadEntity($this->testingMetaEntity['vp_id'], $this->testingParentEntity['vp_id']);
        $this->assertEquals($this->testingMetaEntity, $loadedMetaEntity);
    }

    /**
     * @test
     */
    public function loadAllReturnsOnlyOriginalEntities()
    {
        $this->parentStorage->save($this->testingParentEntity);
        $this->storage->save($this->testingMetaEntity);
        $loadedPostMeta = $this->storage->loadAll();
        $this->assertTrue(count($loadedPostMeta) === 1);
        $this->assertEquals($this->testingMetaEntity, reset($loadedPostMeta));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->storageDir = sys_get_temp_dir() . '/vp-storage-dir';

        $metaEntityInfo = $this->createEntityInfoMock([
            'vpidColumnName' => 'vp_id',
            'usesGeneratedVpids' => true,
            'idColumnName' => 'meta_id',
            'parentReference' => 'parent_id',
        ], [
            'getIgnoredColumns' => [],
        ]);

        $entityInfo = $this->createEntityInfoMock([
            'vpidColumnName' => 'vp_id',
            'usesGeneratedVpids' => true,
            'idColumnName' => 'post_id',
        ], [
            'getIgnoredColumns' => [],
        ]);

        if (file_exists($this->storageDir)) {
            FileSystem::remove($this->storageDir);
        }

        mkdir($this->storageDir);

        $changeInfoFactory = $this->createChangeInfoFactoryMock();

        $this->parentStorage = new DirectoryStorage($this->storageDir, $entityInfo, 'prefix_', $changeInfoFactory);
        $this->storage = new MetaEntityStorage($this->parentStorage, $metaEntityInfo, 'prefix_', $changeInfoFactory);
    }

    protected function tearDown()
    {
        parent::tearDown();
        FileSystem::remove(__DIR__ . '/entities');
    }
}
