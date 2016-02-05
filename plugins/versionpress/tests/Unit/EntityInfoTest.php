<?php

namespace VersionPress\Tests\Unit;

use VersionPress\Database\EntityInfo;

class EntityInfoTest extends \PHPUnit_Framework_TestCase {

    private $entitySchema = array(
        'some-entity' => array(
            'vpid' => 'column',
            'frequently-written' => array(
                'some_field: value other_field: a',
                array(
                    'query' => 'other_field: value',
                    'interval' => '5 min'
                )
            ),
            'ignored-entities' => array(
                'some_field: value other_field: a',
                'other_field: value'
            )
        )
    );

    /** @var EntityInfo */
    private $entityInfo;

    protected function setUp() {
        $this->entityInfo = new EntityInfo($this->entitySchema);
    }

    /**
     * @test
     */
    public function rulesAndIntervalsOfFrequentlyWrittenEntitiesEqualEntitySchema() {
        $expectedRules = array(
            array(
                'rule' =>
                    array(
                        'some_field' => 'value',
                        'other_field' => 'a',
                    ),
                'interval' => 'hourly',
            ),
            array(
                'rule' => array('other_field' => 'value'),
                'interval' => '5 min',
            )
        );

        $this->assertSame($expectedRules, $this->entityInfo->getRulesAndIntervalsForFrequentlyWrittenEntities());
    }

    /**
     * @test
     */
    public function rulesOfIgnoredEntitiesEqualEntitySchema() {
        $expectedRules = array(
            array(
                'some_field' => 'value',
                'other_field' => 'a',
            ),
            array(
                'other_field' => 'value'
            ),
        );

        $this->assertSame($expectedRules, $this->entityInfo->getRulesForIgnoredEntities());
    }

    /**
     * @test
     */
    public function frequentlyWrittenEntityIsCorrectlyIdentified() {
        $entity = array(
            'some_field' => 'value',
            'other_field' => 'a',
        );

        $this->assertTrue($this->entityInfo->isFrequentlyWrittenEntity($entity));
    }

    /**
     * @test
     */
    public function commonEntityIsNotFalselyIdentifiedAsFrequentlyWritten() {
        xdebug_break();
        $entity = array(
            'some_field' => 'value'
        );

        $this->assertFalse($this->entityInfo->isFrequentlyWrittenEntity($entity));
    }

    /**
     * @test
     */
    public function ignoredEntityIsCorrectlyIdentified() {
        $entity = array(
            'some_field' => 'value',
            'other_field' => 'a',
        );

        $this->assertTrue($this->entityInfo->isIgnoredEntity($entity));
    }

    /**
     * @test
     */
    public function commonEntityIsNotFalselyIdentifiedAsIgnored() {
        $entity = array(
            'some_field' => 'value'
        );

        $this->assertFalse($this->entityInfo->isIgnoredEntity($entity));
    }
}
