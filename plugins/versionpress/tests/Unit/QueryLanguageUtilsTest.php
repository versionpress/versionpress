<?php

namespace VersionPress\Tests\Unit;

use VersionPress\Utils\QueryLanguageUtils;

class QueryLanguageUtilsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @dataProvider validQueryAndEntityProvider
     */
    public function entityMatchesRightQuery($queries, $entity)
    {
        $rules = QueryLanguageUtils::createRulesFromQueries($queries);
        $this->assertTrue(QueryLanguageUtils::entityMatchesSomeRule($entity, $rules));
    }

    public function validQueryAndEntityProvider()
    {
        return [
        	// basic rule matching
            [['field: value'], ['field' => 'value']],
            [['field: value'], ['field' => 'value', 'other_field' => 'other_value']],
            [['field: 0'], ['field' => 0]],
            [['field: value other_field: other_value'], ['field' => 'value', 'other_field' => 'other_value']],

	        // wildcards
            [['field: val*'], ['field' => 'value']],
            [['field: *ue'], ['field' => 'value']],
            [['field: v*ue'], ['field' => 'value']],
            [['field: *al*'], ['field' => 'value']],

	        // case insensitivity
            [['FIELD: value'], ['field' => 'value']],
            [['field: VALUE'], ['field' => 'value']],
            [['field: value'], ['FIELD' => 'value']],
            [['field: value'], ['field' => 'VALUE']],
            [['fIElD: VaLuE'], ['fieLD' => 'VAlue']],
        ];
    }

    /**
     * @test
     * @dataProvider wrongQueryAndEntityProvider
     */
    public function entityDoesntMatchWrongQuery($queries, $entity)
    {
        $rules = QueryLanguageUtils::createRulesFromQueries($queries);
        $this->assertFalse(QueryLanguageUtils::entityMatchesSomeRule($entity, $rules));
    }

    public function wrongQueryAndEntityProvider()
    {
        return [
            [['field: value'], ['field' => 'another_value']],
            [['field: value'], ['other_field' => 'value']],
            [['field: value'], ['field' => 0]],
            [['field: value other_field: other_value'], ['field' => 'value']],

            [['field: val*'], ['field' => 'other_value']],
            [['field: *ue'], ['field' => 'value_with_other_suffix']],
            [['field: v*ue'], ['field' => 'other_value']],
            [['field: *al*'], ['field' => 'foo']],
        ];
    }

    /**
     * @test
     * @dataProvider ruleAndQueryProvider
     */
    public function queryLanguageUtilsGeneratesCorrectSqlRestriction($rule, $expectedRestriction)
    {
        $restriction = QueryLanguageUtils::createSqlRestrictionFromRule($rule);
        $this->assertEquals($expectedRestriction, $restriction);
    }

    public function ruleAndQueryProvider()
    {
        return [
            [['field' => ['value']], '(`field` = "value")'],
            [['FIELD' => ['Value']], '(`FIELD` = "Value")'],
            [
                ['field' => ['value'], 'other_field' => ['other_value']],
                '(`field` = "value" AND `other_field` = "other_value")'
            ],

            [['field' => ['val*']], '(`field` LIKE "val%")'],
            [['field' => ['*ue']], '(`field` LIKE "%ue")'],
            [['field' => ['v*ue']], '(`field` LIKE "v%ue")'],
            [['field' => ['*al*']], '(`field` LIKE "%al%")'],

            [
                ['field' => ['*al*'], 'other_field' => ['other_value']],
                '(`field` LIKE "%al%" AND `other_field` = "other_value")'
            ],
            [
                ['field' => ['*al*'], 'other_field' => ['other_*']],
                '(`field` LIKE "%al%" AND `other_field` LIKE "other\_%")'
            ],

            [['field' => ['_*']], '(`field` LIKE "\_%")'],
        ];
    }

    /**
     * @test
     * @dataProvider queryAndRulesProvider
     */
    public function queryLanguageUtilsCreatesCorrectRules($query, $expectedRules)
    {
        $rules = QueryLanguageUtils::createRulesFromQueries($query);
        $this->assertEquals($expectedRules, $rules);
    }

    public function queryAndRulesProvider()
    {
        return [
            [
                ['Text', ' "Longer text" ', '\'Longer text\''],
                [['text' => ['Text']], ['text' => ['Longer text']], ['text' => ['Longer text']]]
            ],
            [
                ['author:doe', ' author: "John Doe" ', 'author:\'John Doe\''],
                [['author' => ['doe']], ['author' => ['John Doe']], ['author' => ['John Doe']]]
            ],
            [
                ['text author:doe "Another text" author: "John" date:>2012-01-02 date: \'2012-01-02 .. 2012-02-13\''],
                [
                    [
                        'author' => ['doe', 'John'],
                        'date' => ['>2012-01-02', '2012-01-02 .. 2012-02-13'],
                        'text' => ['text', 'Another text']
                    ]
                ]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider rulesAndGitLogQueryProvider
     */
    public function queryLanguageUtilsGeneratesCorrectGitLogQuery($rules, $expectedQuery)
    {
        $query = QueryLanguageUtils::createGitLogQueryFromRule($rules);
        $this->assertEquals($expectedQuery, $query);
    }

    public function rulesAndGitLogQueryProvider()
    {
        return [
            [
                ['author' => ['doe', 'do*', 'John Doe']],
                '-i --all-match --author="^doe <.*>$" --author="^do.* <.*>$" --author="^John Doe <.*>$"'
            ],
            [
                ['author' => ['doe@example.com', '*@example.com']],
                '-i --all-match --author="^.* <doe@example\.com>$" --author="^.* <.*@example\.com>$"'
            ],
            [
                ['author' => ['John Doe <doe@example.com>', 'John * <*@*.com>']],
                '-i --all-match --author="^John Doe <doe@example\.com>$" --author="^John .* <.*@.*\.com>$"'
            ],
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
            [['X-VP-another-key' => ['Test value']], '-i --all-match --grep="^X-VP-another-key: \(Test value\)$"'],
            [['vp-another-key' => ['Test value']], '-i --all-match --grep="^\(X-\)\?vp-another-key: \(Test value\)$"'],
            [['another-key' => ['Test value']], '-i --all-match --grep="^\(X-VP-\|VP-\)another-key: \(Test value\)$"'],
            [
                ['*-key' => ['^+?(){|$*\.[']],
                '-i --all-match --grep="^\(X-VP-\|VP-\).*-key: \(^+?(){|\\\\\\$.*\\\\\\\\\.\[\)$"'
            ]
        ];
    }
}
