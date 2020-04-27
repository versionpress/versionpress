<?php

namespace VersionPress\Tests\Unit;

use org\bovigo\vfs\vfsStream;
use Symfony\Component\Yaml\Yaml;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\EntityInfo;
use VersionPress\Tests\Utils\HookMock;

class DbSchemaInfoTest extends \PHPUnit_Framework_TestCase
{
    private $root;

    protected function setUp()
    {
        $this->root = vfsStream::setup();
        HookMock::setUp();
    }

    /**
     * @test
     * @dataProvider schemaProvider
     */
    public function dbSchemaInfoCreatesEntityInfo($schema, $entityName, $idColumnName, $tableName, $references, $vpidColumnName, $hasNaturalId, $mnReferences, $valueReferences, $parentReference)
    {

        $schemaFile = $this->createSchemaFile($schema);
        $schemaInfo = new DbSchemaInfo([$schemaFile], 'prefix_', PHP_INT_MAX);

        $entityInfo = $schemaInfo->getEntityInfo($entityName);

        $this->assertInstanceOf(EntityInfo::class, $entityInfo);
        $this->assertSame($entityName, $entityInfo->entityName);
        $this->assertSame($idColumnName, $entityInfo->idColumnName);
        $this->assertSame($tableName, $entityInfo->tableName);
        $this->assertSame($references, $entityInfo->references);
        $this->assertSame(count($references) + count($mnReferences) + count($valueReferences) > 0, $entityInfo->hasReferences);
        $this->assertSame($vpidColumnName, $entityInfo->vpidColumnName);
        $this->assertSame($hasNaturalId, $entityInfo->hasNaturalVpid);
        $this->assertSame(!$hasNaturalId, $entityInfo->usesGeneratedVpids);
        $this->assertSame($mnReferences, $entityInfo->mnReferences);
        $this->assertSame($valueReferences, $entityInfo->valueReferences);
        $this->assertSame($parentReference, $entityInfo->parentReference);
    }

    public function schemaProvider()
    {
        $simpleEntity = [
            'simple-entity' => [
                'id' => 'entity_id',
            ]
        ];

        $entityWithCustomTable = [
            'some-entity' => [
                'id' => 'entity_id',
                'table' => 'some_table',
            ]
        ];

        $entityWithNaturalKey = [
            'some-entity' => [
                'vpid' => 'some_column',
            ]
        ];

        $entityWithReferences = [
            'some-entity' => [
                'id' => 'entity_id',
                'references' => [
                    'another_id' => 'another-entity'
                ]
            ]
        ];

        $entityWithMnReferences = [
            'some-entity' => [
                'id' => 'entity_id',
                'mn-references' => [
                    'some_table.another_id' => 'another-entity'
                ]
            ]
        ];

        $entityWithValueReferences = [
            'some-entity' => [
                'id' => 'entity_id',
                'value-references' => [
                    'some_column@another_column' => ['some_value' => 'another-entity']
                ]
            ]
        ];

        $metaEntity = [
            'meta-entity' => [
                'id' => 'entity_id',
                'parent-reference' => 'parent_id'
            ]
        ];

        return [
            [$simpleEntity, 'simple-entity', 'entity_id', 'simple-entity', [], 'vp_id', false, [], [], null],
            [$entityWithCustomTable, 'some-entity', 'entity_id', 'some_table', [], 'vp_id', false, [], [], null],
            [$entityWithNaturalKey, 'some-entity', 'some_column', 'some-entity', [], 'some_column', true, [], [], null],
            [$entityWithReferences, 'some-entity', 'entity_id', 'some-entity', $entityWithReferences['some-entity']['references'], 'vp_id', false, [], [], null],
            [$entityWithMnReferences, 'some-entity', 'entity_id', 'some-entity', [], 'vp_id', false, $entityWithMnReferences['some-entity']['mn-references'], [], null],
            [$entityWithValueReferences, 'some-entity', 'entity_id', 'some-entity', [], 'vp_id', false, [], ['some_column=some_value@another_column' => 'another-entity'], null],
            [$metaEntity, 'meta-entity', 'entity_id', 'meta-entity', [], 'vp_id', false, [], [], 'parent_id'],

        ];
    }

    /**
     * @test
     */
    public function dbSchemaInfoMergesReferencesFromMultipleSources()
    {
        $schema1 = [
            'some-entity' => [
                'id' => 'some_column',
                'value-references' => [
                    'some_column@another_column' => ['some_value' => 'another-entity']
                ]
            ]
        ];

        $schema2 = [
            'some-entity' => [
                'value-references' => [
                    'some_column@another_column' => ['another_value' => 'another-entity']
                ]
            ]
        ];

        $schemaFile1 = $this->createSchemaFile($schema1);
        $schemaFile2 = $this->createSchemaFile($schema2);

        $schemaInfo = new DbSchemaInfo([$schemaFile1, $schemaFile2], 'prefix_', PHP_INT_MAX);

        $entityInfo = $schemaInfo->getEntityInfo('some-entity');


        $expectedValueReferences = [
            'some_column=some_value@another_column' => 'another-entity',
            'some_column=another_value@another_column' => 'another-entity',
        ];

        $this->assertSame($expectedValueReferences, $entityInfo->valueReferences);

    }


    /**
     * Creates a virtual file containing YAML created from $scopesDefinition and returns its path.
     *
     * @param array $scopesDefinition
     * @return string
     */
    private function createSchemaFile($scopesDefinition)
    {
        static $fileNumber = 0;

        $fileName = 'actions-' . ($fileNumber++) . '.yml';
        $actionFile = vfsStream::newFile($fileName)->at($this->root);

        file_put_contents($actionFile->url(), Yaml::dump($scopesDefinition));
        return $actionFile->url();
    }
}
