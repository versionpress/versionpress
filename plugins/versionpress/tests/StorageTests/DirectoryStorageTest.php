<?php

namespace VersionPress\Tests\StorageTests;

use VersionPress\Storages\DirectoryStorage;
use VersionPress\Utils\FileSystem;

class DirectoryStorageTest extends StorageTestCase
{
    /** @var DirectoryStorage */
    private $storage;

    private $testingEntity = [
        "comment_author" => "Mr WordPress",
        "comment_author_email" => "",
        "comment_author_url" => "https://wordpress.org/",
        "comment_author_IP" => "",
        "comment_date" => "2015-02-02 14:19:59",
        "comment_date_gmt" => "2015-02-02 14:19:59",
        "comment_content" => "Hi, this is a comment.",
        "comment_karma" => 0,
        "comment_approved" => 1,
        "comment_agent" => "",
        "comment_type" => "",
        "vp_id" => "927D63C187164CA1BCEAB2B13B29C8F0",
        "vp_comment_post_ID" => "F0E1B6313B7A48E49A1B38DF382B350D",
    ];

    /**
     * @test
     */
    public function savedEntityEqualsLoadedEntity()
    {
        $this->storage->save($this->testingEntity);
        $loadedEntity = $this->storage->loadEntity($this->testingEntity['vp_id']);
        $this->assertEquals($this->testingEntity, $loadedEntity);
    }


    /**
     * @test
     */
    public function loadAllReturnsOnlyOriginalEntities()
    {
        $this->storage->save($this->testingEntity);
        $loadedComments = $this->storage->loadAll();
        $this->assertTrue(count($loadedComments) === 1);
        $this->assertTrue($this->testingEntity == reset($loadedComments));
    }

    /**
     * @test
     */
    public function storageDoesNotContainDeletedEntities()
    {
        $anotherTestingEntity = $this->testingEntity;
        $anotherTestingEntity['another_field'] = 'value';
        $anotherTestingEntity['vp_id'] = '0123456789ABCDEFFEDCBA9876543210';

        $this->storage->save($this->testingEntity);
        $this->storage->save($anotherTestingEntity);

        $loadedEntities = $this->storage->loadAll();
        $this->assertTrue(count($loadedEntities) === 2);

        $this->storage->delete($this->testingEntity);

        $loadedEntities = $this->storage->loadAll();
        $this->assertTrue(count($loadedEntities) === 1);
        $this->assertEquals($anotherTestingEntity, reset($loadedEntities));
    }

    /**
     * @test
     */
    public function savedEntityDoesNotContainVpIdKey()
    {
        $this->storage->save($this->testingEntity);
        $fileName = $this->storage->getEntityFilename($this->testingEntity['vp_id']);
        $content = file_get_contents($fileName);
        $this->assertFalse(strpos($content, 'vp_id'), 'Entity contains a vp_id key');
    }

    /**
     * @test
     */
    public function savedEntityDoesNotContainPrimaryKey()
    {
        $testingEntity = $this->testingEntity;
        $testingEntity['comment_id'] = 123;

        $this->storage->save($testingEntity);
        $loadedEntity = $this->storage->loadEntity($this->testingEntity['vp_id']);
        $this->assertEquals($this->testingEntity, $loadedEntity);
    }

    /**
     * @test
     */
    public function savedEntityDoesNotContainIgnoredColumns()
    {
        $testingEntity = $this->testingEntity;
        $testingEntity['ignored_column'] = 123;

        $this->storage->save($testingEntity);
        $loadedEntity = $this->storage->loadEntity($this->testingEntity['vp_id']);
        $this->assertEquals($this->testingEntity, $loadedEntity);
    }

    /**
     * @test
     */
    public function typesOfSavedAndLoadedEntityMatch()
    {
        $testingEntity = [
            'field_1' => 'some option',
            'field_2' => null,
            'field_3' => 1,
            'vp_id' => '1234ABCD'
        ];
        $this->storage->save($testingEntity);
        $loadedEntity = $this->storage->loadEntity($testingEntity['vp_id']);
        $this->assertSame($testingEntity, $loadedEntity);
    }

    /**
     * @test
     * @dataProvider specialIdsProvider
     */
    public function naturalIdCanContainSpecialChars($id)
    {
        $idColumnName = 'entity_id';

        $entity = [
            $idColumnName => $id,
            'some_field' => 'foo',
            'other_field' => 'bar',
        ];

        $entityInfo = $this->createEntityInfoMock([
            'usesGeneratedVpids' => false,
            'vpidColumnName' => $idColumnName,
        ], [
            'getIgnoredColumns' => [],
        ]);
        $actionsInfo = $this->createActionsInfoMock();

        $storage = new DirectoryStorage(__DIR__ . '/entities', $entityInfo, 'prefix_', $actionsInfo);

        $storage->save($entity);
        $loadedEntity = $storage->loadEntity($id);
        $this->assertEquals($entity, $loadedEntity);
    }

    public function specialIdsProvider()
    {
        return [
            ['name_with_<'],
            ['name_with_>'],
            ['name_with_:'],
            ['name_with_?'],
            ['name_with_*'],
            ['name_with_|'],
            ['name_with_"'],
            ['name_with_/'],
            ['name_with_\\'],
            ['.'],
            ['..'],
            [' '],
            ['+'],
            ['%2B'],
        ];
    }

    /**
     * Test covers part of for WP-428
     *
     * @test
     */
    public function vpidShouldBeReplaceableWithZero()
    {
        $updatedEntity = $this->testingEntity;

        $updatedEntity['vp_comment_post_ID'] = 0;

        $this->storage->save($this->testingEntity);
        $this->storage->save($updatedEntity);

        $loadedOption = $this->storage->loadEntity($updatedEntity['vp_id']);
        $this->assertEquals($updatedEntity, $loadedOption);
    }

    protected function setUp()
    {
        parent::setUp();

        $storageDir = __DIR__ . '/entities';
        $entityInfo = $this->createEntityInfoMock([
            'vpidColumnName' => 'vp_id',
            'usesGeneratedVpids' => true,
            'idColumnName' => 'comment_id',
        ], [
            'getIgnoredColumns' => ['ignored_column' => null],
        ]);
        $actionsInfo = $this->createActionsInfoMock();


        if (file_exists($storageDir)) {
            FileSystem::remove($storageDir);
        }

        mkdir($storageDir);
        $this->storage = new DirectoryStorage($storageDir, $entityInfo, 'prefix_', $actionsInfo);
    }

    protected function tearDown()
    {
        parent::tearDown();
        FileSystem::remove(__DIR__ . '/entities');
    }
}
