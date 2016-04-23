<?php

namespace VersionPress\Tests\Unit;

use VersionPress\Database\EntityInfo;

class EntityInfoTest extends \PHPUnit_Framework_TestCase
{

    private $entitySchema = [
        'some-entity' => [
            'vpid' => 'column',
            'frequently-written' => [
                'some_field: value other_field: a',
                [
                    'query' => 'other_field: value',
                    'interval' => '5 min'
                ]
            ],
            'ignored-entities' => [
                'some_field: value other_field: a',
                'other_field: value'
            ],
            'ignored-columns' => [
                'ignored_column',
                ['other_ignored_column' => '@\SomeNamespace\SomeClass::someFunction']
            ]
        ]
    ];

    /** @var EntityInfo */
    private $entityInfo;

    protected function setUp()
    {
        $this->entityInfo = new EntityInfo($this->entitySchema);
    }

    /**
     * @test
     */
    public function rulesAndIntervalsOfFrequentlyWrittenEntitiesEqualEntitySchema()
    {
        $expectedRules = [
            [
                'rule' =>
                    [
                        'some_field' => ['value'],
                        'other_field' => ['a'],
                    ],
                'interval' => 'hourly',
            ],
            [
                'rule' => ['other_field' => ['value']],
                'interval' => '5 min',
            ]
        ];

        $this->assertSame($expectedRules, $this->entityInfo->getRulesAndIntervalsForFrequentlyWrittenEntities());
    }

    /**
     * @test
     */
    public function rulesOfIgnoredEntitiesEqualEntitySchema()
    {
        $expectedRules = [
            [
                'some_field' => ['value'],
                'other_field' => ['a'],
            ],
            [
                'other_field' => ['value']
            ],
        ];

        $this->assertSame($expectedRules, $this->entityInfo->getRulesForIgnoredEntities());
    }

    /**
     * @test
     */
    public function frequentlyWrittenEntityIsCorrectlyIdentified()
    {
        $entity = [
            'some_field' => 'value',
            'other_field' => 'a',
        ];

        $this->assertTrue($this->entityInfo->isFrequentlyWrittenEntity($entity));
    }

    /**
     * @test
     */
    public function commonEntityIsNotFalselyIdentifiedAsFrequentlyWritten()
    {
        $entity = [
            'some_field' => 'value'
        ];

        $this->assertFalse($this->entityInfo->isFrequentlyWrittenEntity($entity));
    }

    /**
     * @test
     */
    public function ignoredEntityIsCorrectlyIdentified()
    {
        $entity = [
            'some_field' => 'value',
            'other_field' => 'a',
        ];

        $this->assertTrue($this->entityInfo->isIgnoredEntity($entity));
    }

    /**
     * @test
     */
    public function commonEntityIsNotFalselyIdentifiedAsIgnored()
    {
        $entity = [
            'some_field' => 'value'
        ];

        $this->assertFalse($this->entityInfo->isIgnoredEntity($entity));

        $entity = [
            'some_field' => 'value',
            'other_field' => 'b',
        ];

        $this->assertFalse($this->entityInfo->isIgnoredEntity($entity));
    }

    /**
     * @test
     */
    public function ignoredColumnsAreIdentifiedCorrectly()
    {
        $this->assertEquals([], array_diff(
            ['ignored_column', 'other_ignored_column'],
            array_keys($this->entityInfo->getIgnoredColumns())
        ));
    }
}
