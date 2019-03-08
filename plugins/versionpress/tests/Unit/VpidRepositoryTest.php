<?php

namespace VersionPress\Tests\Unit;

use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\EntityInfo;
use VersionPress\Database\VpidRepository;

class VpidRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function replacingSimpleValueReference()
    {
        $databaseMock = \Mockery::mock(Database::class);
        $schemaInfoMock = \Mockery::mock(DbSchemaInfo::class);

        $fooSchema = [
            'table' => 'foo',
            'id' => 'id',
            'value-references' => [
                'name@value' => [
                    'bar' => 'bar',
                ],
            ],
        ];

        $schemaInfoMock->shouldReceive('getEntityInfo')->with('foo')->andReturn(new EntityInfo(['foo' => $fooSchema]));
        $schemaInfoMock->shouldReceive('getTableName')->with('bar')->andReturn('bar');

        $databaseMock->shouldReceive('get_var')->andReturn('vpid3456');

        $vpidRepository = new VpidRepository($databaseMock, $schemaInfoMock);

        $entity = [
            'id' => 1234,
            'name' => 'bar',
            'value' => 3456,
        ];
        $replacedEntity = $vpidRepository->replaceForeignKeysWithReferences('foo', $entity);
        $expectedEntity = [
            'id' => 1234,
            'name' => 'bar',
            'value' => 'vpid3456',
        ];
        
        $nullEntity = [
            'id' => 4321,
            'name' => 'bar',
            'value' => -1,
        ];
        $replacedNullEntity = $vpidRepository->replaceForeignKeysWithReferences('foo', $nullEntity);

        $this->assertSame($expectedEntity, $replacedEntity);
        $this->assertSame($nullEntity, $replacedNullEntity);
    }

    /** @test */
    public function replacingReferenceInSerializedData()
    {
        $databaseMock = \Mockery::mock(Database::class);
        $schemaInfoMock = \Mockery::mock(DbSchemaInfo::class);

        $fooSchema = [
            'table' => 'foo',
            'id' => 'id',
            'value-references' => [
                'name@value' => [
                    'bar["id"]' => 'bar',
                ],
            ],
        ];

        $schemaInfoMock->shouldReceive('getEntityInfo')->with('foo')->andReturn(new EntityInfo(['foo' => $fooSchema]));
        $schemaInfoMock->shouldReceive('getTableName')->with('bar')->andReturn('bar');

        $databaseMock->shouldReceive('get_var')->andReturn('vpid3456');

        $vpidRepository = new VpidRepository($databaseMock, $schemaInfoMock);

        $entity = [
            'id' => 1234,
            'name' => 'bar',
            'value' => serialize(['id' => 3456]),
        ];
        $replacedEntity = $vpidRepository->replaceForeignKeysWithReferences('foo', $entity);
        $expectedEntity = [
            'id' => 1234,
            'name' => 'bar',
            'value' => serialize(['id' => 'vpid3456']),
        ];

        $nullEntity = [
            'id' => 4321,
            'name' => 'bar',
            'value' => serialize(['id' => -1]),
        ];
        $replacedNullEntity = $vpidRepository->replaceForeignKeysWithReferences('foo', $nullEntity);

        $this->assertSame($expectedEntity, $replacedEntity);
        $this->assertSame($nullEntity, $replacedNullEntity);
    }

    /** @test */
    public function replacingReferenceInSerializedDataWithWildcard()
    {
        $databaseMock = \Mockery::mock(Database::class);
        $schemaInfoMock = \Mockery::mock(DbSchemaInfo::class);

        $fooSchema = [
            'table' => 'foo',
            'id' => 'id',
            'value-references' => [
                'name@value' => [
                    'bar_*["id"]' => 'bar',
                ],
            ],
        ];

        $schemaInfoMock->shouldReceive('getEntityInfo')->with('foo')->andReturn(new EntityInfo(['foo' => $fooSchema]));
        $schemaInfoMock->shouldReceive('getTableName')->with('bar')->andReturn('bar');

        $databaseMock->shouldReceive('get_var')->andReturn('vpid3456');

        $vpidRepository = new VpidRepository($databaseMock, $schemaInfoMock);

        $entity = [
            'id' => 1234,
            'name' => 'bar_something',
            'value' => serialize(['id' => 3456]),
        ];
        $replacedEntity = $vpidRepository->replaceForeignKeysWithReferences('foo', $entity);
        $expectedEntity = [
            'id' => 1234,
            'name' => 'bar_something',
            'value' => serialize(['id' => 'vpid3456']),
        ];

        $this->assertSame($expectedEntity, $replacedEntity);
    }

    /** @test */
    public function replacingReferenceInSerializedDataWithRegex()
    {
        $databaseMock = \Mockery::mock(Database::class);
        $schemaInfoMock = \Mockery::mock(DbSchemaInfo::class);

        $fooSchema = [
            'table' => 'foo',
            'id' => 'id',
            'value-references' => [
                'name@value' => [
                    'bar_*[/\d+/]' => 'bar',
                ],
            ],
        ];

        $schemaInfoMock->shouldReceive('getEntityInfo')->with('foo')->andReturn(new EntityInfo(['foo' => $fooSchema]));
        $schemaInfoMock->shouldReceive('getTableName')->with('bar')->andReturn('bar');

        $databaseMock->shouldReceive('get_var')->andReturn('vpid3456');

        $vpidRepository = new VpidRepository($databaseMock, $schemaInfoMock);

        $entity = [
            'id' => 1234,
            'name' => 'bar_something',
            'value' => serialize([3456]),
        ];
        $replacedEntity = $vpidRepository->replaceForeignKeysWithReferences('foo', $entity);
        $expectedEntity = [
            'id' => 1234,
            'name' => 'bar_something',
            'value' => serialize(['vpid3456']),
        ];

        $this->assertSame($expectedEntity, $replacedEntity);
    }

    /** @test */
    public function replacingAllReferenceInSerializedDataWithRegex()
    {
        $databaseMock = \Mockery::mock(Database::class);
        $schemaInfoMock = \Mockery::mock(DbSchemaInfo::class);

        $fooSchema = [
            'table' => 'foo',
            'id' => 'id',
            'value-references' => [
                'name@value' => [
                    'bar_*[/\d+/]' => 'bar',
                ],
            ],
        ];

        $schemaInfoMock->shouldReceive('getEntityInfo')->with('foo')->andReturn(new EntityInfo(['foo' => $fooSchema]));
        $schemaInfoMock->shouldReceive('getTableName')->with('bar')->andReturn('bar');

        $databaseMock->shouldReceive('get_var')->once()->andReturn('vpid3456');
        $databaseMock->shouldReceive('get_var')->once()->andReturn('vpid5678');

        $vpidRepository = new VpidRepository($databaseMock, $schemaInfoMock);

        $entity = [
            'id' => 1234,
            'name' => 'bar_something',
            'value' => serialize([3456, 5678]),
        ];
        $replacedEntity = $vpidRepository->replaceForeignKeysWithReferences('foo', $entity);
        $expectedEntity = [
            'id' => 1234,
            'name' => 'bar_something',
            'value' => serialize(['vpid3456', 'vpid5678']),
        ];

        $this->assertSame($expectedEntity, $replacedEntity);
    }

    /** @test */
    public function replacingReferenceInSerializedDataInSerializedData()
    {
        $databaseMock = \Mockery::mock(Database::class);
        $schemaInfoMock = \Mockery::mock(DbSchemaInfo::class);

        $fooSchema = [
            'table' => 'foo',
            'id' => 'id',
            'value-references' => [
                'name@value' => [
                    'bars["baz"]..["id"]' => 'baz',
                ],
            ],
        ];

        $schemaInfoMock->shouldReceive('getEntityInfo')->with('foo')->andReturn(new EntityInfo(['foo' => $fooSchema]));
        $schemaInfoMock->shouldReceive('getTableName')->with('baz')->andReturn('baz');

        $databaseMock->shouldReceive('get_var')->andReturn('vpid3456');

        $vpidRepository = new VpidRepository($databaseMock, $schemaInfoMock);

        $entity = [
            'id' => 1234,
            'name' => 'bars',
            'value' => serialize(['baz' => serialize(['id' => 3456])]),
        ];
        $replacedEntity = $vpidRepository->replaceForeignKeysWithReferences('foo', $entity);
        $expectedEntity = [
            'id' => 1234,
            'name' => 'bars',
            'value' => serialize(['baz' => serialize(['id' => 'vpid3456'])]),
        ];

        $this->assertSame($expectedEntity, $replacedEntity);
    }
}