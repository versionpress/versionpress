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
        // Perform case insensitive match
        $this->assertEquals($expectedRules, $rules, '', 0, 10, FALSE, TRUE);
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
        // Perform case insensitive match
        $this->assertEquals($expectedQuery, $query, '', 0, 10, FALSE, TRUE);
    }

    public function rulesAndGitLogQueryProvider() {
        return [
            [['author' => ['doe', 'do*', 'John Doe']], '-i --all-match --author="^doe <.*>$" --author="^do.* <.*>$" --author="^John Doe <.*>$"'],
            [['author' => ['doe@example.com', '*@example.com']], '-i --all-match --author="^.* <doe@example\.com>$" --author="^.* <.*@example\.com>$"'],
            [['author' => ['John Doe <doe@example.com>', 'John * <*@*.com>']], '-i --all-match --author="^John Doe <doe@example\.com>$" --author="^John .* <.*@.*\.com>$"'],
            [['date' => ['>2012-01-02']], '-i --all-match --after=2012-01-02'],
            [['date' => ['>=2012-01-02']], '-i --all-match --after=2012-01-01'],
            [['date' => ['<2012-01-02']], '-i --all-match --before=2012-01-01'],
            [['date' => ['<=2012-01-02']], '-i --all-match --before=2012-01-02'],
            [['date' => ['2012-01-02 .. 2012-02-13']], '-i --all-match --after=2012-01-01 --before=2012-02-14'],
            [['date' => ['2012-01-02 .. *']], '-i --all-match --after=2012-01-01'],
            [['date' => ['* .. 2012-02-13']], '-i --all-match --before=2012-02-14'],
            [['action' => ['entity/action']], '-i --all-match --grep="^VP-Action: \(entity/action\)\(/.*\)\?$"'],
            [['vp-action' => ['entity/*']], '-i --all-match --grep="^VP-Action: \(entity/.*\)\(/.*\)\?$"'],
            [['action' => ['*/action/*']], '-i --all-match --grep="^VP-Action: \(.*/action/.*\)\(/.*\)\?$"'],
            [['vp-action' => ['entity/*/vpid']], '-i --all-match --grep="^VP-Action: \(entity/.*/vpid\)\(/.*\)\?$"'],
            [['entity' => ['entity']], '-i --all-match --grep="^VP-Action: \(entity\)/.*\(/.*\)\?$"'],
            [['action' => ['action']], '-i --all-match --grep="^VP-Action: .*/\(action\)\(/.*\)\?$"'],
            [['vpid' => ['vpid']], '-i --all-match --grep="^VP-Action: .*/.*/\(vpid\)$"'],
            [
                ['entity' => ['entity', 'entity2'], 'action' => ['action', 'long action'], 'vpid' => ['vpid', 'vpid2']],
                '-i --all-match --grep="^VP-Action: \(entity\|entity2\)/\(action\|long action\)/\(vpid\|vpid2\)$"'
            ],
            [
                ['entity' => ['entity', '*'], 'vpid' => ['*vp*', 'vpid']],
                '-i --all-match --grep="^VP-Action: \(entity\|.*\)/.*/\(.*vp.*\|vpid\)$"'
            ],
            [['text' => ['text1', 'Test text', '*']], '-i --all-match --grep="text1" --grep="Test text" --grep=".*"'],
            [['x-vp-another-key' => ['Test value']], '-i --all-match --grep="^x-vp-another-key: \(Test value\)$"'],
            [['vp-another-key' => ['Test value']], '-i --all-match --grep="^\(x-\)\?vp-another-key: \(Test value\)$"'],
            [['another-key' => ['Test value']], '-i --all-match --grep="^\(x-vp-\|vp-\)another-key: \(Test value\)$"'],
            [['*-key' => ['^+?(){|$*\.[']], '-i --all-match --grep="^\(x-vp-\|vp-\).*-key: \(^+?(){|\\\\\\$.*\\\\\\\\\.\[\)$"']
        ];
    }
}
