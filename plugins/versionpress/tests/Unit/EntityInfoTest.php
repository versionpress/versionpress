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
                'capitalized_value_field: VALUE',
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
                'capitalized_value_field' => ['VALUE']
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
    public function partialMatchIsNotFalselyIdentifiedAsFrequentlyWritten()
    {
        // The rule is `'some_field: value other_field: a'` and the entity only partially matches it:
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
    public function ignoredEntityCapitalizedIsCorrectlyIdentified()
    {
        $entity = [
            'some_field' => 'b',
            'other_field' => 'a',
            'capitalized_value_field' => 'VALUE',
        ];

        $this->assertTrue($this->entityInfo->isIgnoredEntity($entity));
    }

    /**
     * @test
     */
    public function partialMatchIsNotFalselyIdentifiedAsIgnored()
    {
        // The rule is `'some_field: value other_field: a'` and the entity must match it entirely.

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
