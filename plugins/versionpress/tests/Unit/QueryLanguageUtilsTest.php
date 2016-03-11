<?php

namespace VersionPress\Tests\Unit;

use VersionPress\Utils\QueryLanguageUtils;

class QueryLanguageUtilsTest extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     * @dataProvider validQueryAndEntityProvider
     */
    public function entityMatchesRightQuery($queries, $entity) {
        $rules = QueryLanguageUtils::createRulesFromQueries($queries);
        $this->assertTrue(QueryLanguageUtils::entityMatchesSomeRule($entity, $rules));
    }

    public function validQueryAndEntityProvider() {
        return array(
            array(array('field: value'), array('field' => 'value')),
            array(array('field: value'), array('field' => 'value', 'other_field' => 'other_value')),
            array(array('field: value other_field: other_value'), array('field' => 'value', 'other_field' => 'other_value')),

            array(array('field: val*'), array('field' => 'value')),
            array(array('field: *ue'), array('field' => 'value')),
            array(array('field: v*ue'), array('field' => 'value')),
            array(array('field: *al*'), array('field' => 'value')),
        );
    }

    /**
     * @test
     * @dataProvider wrongQueryAndEntityProvider
     */
    public function entityDoesntMatchWrongQuery($queries, $entity) {
        $rules = QueryLanguageUtils::createRulesFromQueries($queries);
        $this->assertFalse(QueryLanguageUtils::entityMatchesSomeRule($entity, $rules));
    }

    public function wrongQueryAndEntityProvider() {
        return array(
            array(array('field: value'), array('field' => 'another_value')),
            array(array('field: value'), array('other_field' => 'value')),
            array(array('field: value other_field: other_value'), array('field' => 'value')),

            array(array('field: val*'), array('field' => 'other_value')),
            array(array('field: *ue'), array('field' => 'value_with_other_suffix')),
            array(array('field: v*ue'), array('field' => 'other_value')),
            array(array('field: *al*'), array('field' => 'foo')),
        );
    }

    /**
     * @test
     * @dataProvider ruleAndQueryProvider
     */
    public function queryLanguageUtilsGeneratesCorrectSqlRestriction($rule, $expectedRestriction) {
        $restriction = QueryLanguageUtils::createSqlRestrictionFromRule($rule);
        $this->assertEquals($expectedRestriction, $restriction);
    }

    public function ruleAndQueryProvider() {
        return array(
            array(array('field' => array('value')), '(`field` = "value")'),
            array(array('field' => array('value'), 'other_field' => array('other_value')), '(`field` = "value" AND `other_field` = "other_value")'),

            array(array('field' => array('val*')), '(`field` LIKE "val%")'),
            array(array('field' => array('*ue')), '(`field` LIKE "%ue")'),
            array(array('field' => array('v*ue')), '(`field` LIKE "v%ue")'),
            array(array('field' => array('*al*')), '(`field` LIKE "%al%")'),

            array(array('field' => array('*al*'), 'other_field' => array('other_value')), '(`field` LIKE "%al%" AND `other_field` = "other_value")'),
            array(array('field' => array('*al*'), 'other_field' => array('other_*')), '(`field` LIKE "%al%" AND `other_field` LIKE "other\_%")'),

            array(array('field' => array('_*')), '(`field` LIKE "\_%")'),
        );
    }
}
