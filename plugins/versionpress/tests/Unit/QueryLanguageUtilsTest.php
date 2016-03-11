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

    /**
     * @test
     * @dataProvider queryAndRulesProvider
     */
    public function queryLanguageUtilsCreatesCorrectRules($query, $expectedRules) {
        $rules = QueryLanguageUtils::createRulesFromQueries($query);
        $this->assertEquals($expectedRules, $rules);
    }

    public function queryAndRulesProvider() {
        return array(
            array(
                array('Text', ' "Longer text" ', '\'Longer text\''),
                array(
                    array('text' => array('Text')),
                    array('text' => array('Longer text')),
                    array('text' => array('Longer text'))
                )
            ),
            array(
                array('author:doe', ' author: "John Doe" ', 'author:\'John Doe\''),
                array(
                    array('author' => array('doe')),
                    array('author' => array('John Doe')),
                    array('author' => array('John Doe'))
                )
            ),
            array(
                array('text author:doe "Another text" author: "John Doe" date:>2012-01-02 date: \'2012-01-02 .. 2012-02-13\''),
                array(
                    array(
                        'author' => array('doe', 'John Doe'),
                        'date' => array('>2012-01-02', '2012-01-02 .. 2012-02-13'),
                        'text' => array('text', 'Another text')
                    )
                )
            )
        );
    }
    
    /**
     * @test
     * @dataProvider rulesAndGitLogQueryProvider
     */
    public function queryLanguageUtilsGeneratesCorrectGitLogQuery($rules, $expectedQuery) {
        $query = QueryLanguageUtils::createGitLogQueryFromRule($rules);
        $this->assertEquals($expectedQuery, $query);
    }

    public function rulesAndGitLogQueryProvider() {
        return array(
            array(
                array('author' => array('doe', 'John Doe')),
                '-i --all-match --author="doe" --author="John Doe"'
            ),
            array(array('date' => array('>2012-01-02')), '-i --all-match --after=2012-01-02'),
            array(array('date' => array('>=2012-01-02')), '-i --all-match --after=2012-01-01'),
            array(array('date' => array('<2012-01-02')), '-i --all-match --before=2012-01-01'),
            array(array('date' => array('<=2012-01-02')), '-i --all-match --before=2012-01-02'),
            array(
                array('date' => array('2012-01-02 .. 2012-02-13')),
                '-i --all-match --after=2012-01-01 --before=2012-02-14'
            ),
            array(
                array('date' => array('2012-01-02 .. *')), '-i --all-match --after=2012-01-01'
            ),
            array(
                array('date' => array('* .. 2012-02-13')), '-i --all-match --before=2012-02-14'
            ),
            array(array('entity' => array('entity')), '-i --all-match --grep="^VP-Action: \(entity\)/.*/.*"'),
            array(array('action' => array('action')), '-i --all-match --grep="^VP-Action: .*/\(action\)/.*"'),
            array(array('vpid' => array('vpid')), '-i --all-match --grep="^VP-Action: .*/.*/\(vpid\)"'),
            array(
                array(
                    'entity' => array('entity', 'entity2'),
                    'action' => array('action', 'long action'),
                    'vpid' => array('vpid', 'vpid2')
                ),
                '-i --all-match --grep="^VP-Action: \(entity\|entity2\)/\(action\|long action\)/\(vpid\|vpid2\)"'
            ),
            array(
                array('text' => array('text1', 'Test text')), '-i --all-match --grep="\(text1\|Test text\)"'
            ),
            array(
                array('key' => array('Test value')), '-i --all-match --grep="^vp-key: \(Test value\)"'
            )
        );
    }
}
