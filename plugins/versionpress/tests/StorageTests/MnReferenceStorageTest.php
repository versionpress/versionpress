<?php

namespace VersionPress\Tests\StorageTests;

use VersionPress\Storages\DirectoryStorage;
use VersionPress\Storages\MnReferenceStorage;
use VersionPress\Utils\FileSystem;

class MnReferenceStorageTest extends StorageTestCase
{

    /** @var MnReferenceStorage */
    private $storage;
    /** @var DirectoryStorage */
    private $parentStorage;

    /**
     * @test
     */
    public function parentEntityContainsReferenceAfterItsSaved()
    {
        $parent = [
            'vp_id' => '927D63C187164CA1BCEAB2B13B29C8F0',
            'some_field' => 'some_value',
        ];

        $mnReference = [
            'vp_some_entity' => '927D63C187164CA1BCEAB2B13B29C8F0',
            'vp_other_entity' => 'F0E1B6313B7A48E49A1B38DF382B350D',
         ];

        $this->parentStorage->save($parent);
        $this->storage->save($mnReference);

        $expectedParent = $parent;
        $expectedParent['vp_other_entity'][0] = $mnReference['vp_other_entity'];

        $loadedParent = $this->parentStorage->loadEntity($mnReference['vp_some_entity']);

        $this->assertEquals($expectedParent, $loadedParent);
    }

    /**
     * @test
     */
    public function parentEntityContainsReferenceOnlyOnce()
    {
        $parent = [
            'vp_id' => '927D63C187164CA1BCEAB2B13B29C8F0',
            'some_field' => 'some_value',
        ];

        $mnReference = [
            'vp_some_entity' => '927D63C187164CA1BCEAB2B13B29C8F0',
            'vp_other_entity' => 'F0E1B6313B7A48E49A1B38DF382B350D',
        ];

        $this->parentStorage->save($parent);
        $this->storage->save($mnReference);
        $this->storage->save($mnReference);

        $loadedParent = $this->parentStorage->loadEntity($mnReference['vp_some_entity']);

        $this->assertCount(1, $loadedParent['vp_other_entity']);
    }

    /**
     * @test
     */
    public function parentEntityDoesNotContainReferenceAfterItsDeleted()
    {
        $parent = [
            'vp_id' => '927D63C187164CA1BCEAB2B13B29C8F0',
            'some_field' => 'some_value',
        ];

        $mnReference = [
            'vp_some_entity' => '927D63C187164CA1BCEAB2B13B29C8F0',
            'vp_other_entity' => 'F0E1B6313B7A48E49A1B38DF382B350D',
        ];

        $this->parentStorage->save($parent);
        $this->storage->save($mnReference);
        $this->storage->delete($mnReference);

        $loadedParent = $this->parentStorage->loadEntity($mnReference['vp_some_entity']);
        $this->assertArrayNotHasKey('vp_other_entity', $loadedParent);
    }

    protected function setUp()
    {
        parent::setUp();

        $storageDir = __DIR__ . '/entities';
        $entityInfo = $this->createEntityInfoMock([
            'vpidColumnName' => 'vp_id',
            'usesGeneratedVpids' => true,
            'idColumnName' => 'entity_id',
        ], [
            'getIgnoredColumns' => ['ignored_column' => null],
        ]);

        $referenceDetails = [
            'junction-table' => 'junction_table',
            'source-entity' => 'some_entity',
            'source-column' => 'some_column',
            'target-entity' => 'other_entity',
            'target-column' => 'other_column',
        ];


        if (file_exists($storageDir)) {
            FileSystem::remove($storageDir);
        }

        mkdir($storageDir);

        $changeInfoFactory = $this->createChangeInfoFactoryMock();
        $this->parentStorage = new DirectoryStorage($storageDir, $entityInfo, 'prefix_', $changeInfoFactory);
        $this->storage = new MnReferenceStorage($this->parentStorage, $referenceDetails);
    }
}
