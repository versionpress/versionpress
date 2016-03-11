<?php

namespace VersionPress\Tests\SqlTests;

use PHPUnit_Framework_MockObject_Generator;
use PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount;
use PHPUnit_Framework_MockObject_Stub_Return;
use PHPUnit_Framework_TestCase;
use SqlParser\Parser;
use SqlParser\Statements\DeleteStatement;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\ParsedQueryData;
use VersionPress\Database\SqlQueryParser;

class ParserTest extends PHPUnit_Framework_TestCase {

    /**
     * @var DbSchemaInfo
     */
    private static $DbSchemaInfo;

    /** @var \wpdb $wpdbStub */
    private $wpdbStub;

    /** @var  SqlQueryParser */
    private $sqlParser;

    public static function setUpBeforeClass() {
        self::$DbSchemaInfo = new DbSchemaInfo(__DIR__ . '/../../src/Database/wordpress-schema.yml', 'wp_', PHP_INT_MAX);
    }

    public function setup() {
        $this->wpdbStub = $this->getMockBuilder('\wpdb')->disableOriginalConstructor()->getMock();
        $this->sqlParser = new SqlQueryParser(self::$DbSchemaInfo, $this->wpdbStub);
    }


    /**
     * @test
     * @dataProvider updateQueryParseTestDataProvider
     * @param $query
     * @param $expectedSelect
     * @param $expectedData
     */
    public function dataToSetFromUpdate($query, $expectedSelect, $expectedData) {

        $this->wpdbStub->expects(new PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)->method("get_col");
        $parsedQueryData = $this->sqlParser->parseQuery($query, self::$DbSchemaInfo, $this->wpdbStub);

        $this->assertEquals($expectedData, $parsedQueryData->data);

    }


    /**
     * @test
     * @dataProvider updateQueryParseTestDataProvider
     * @param $query
     * @param $expectedSelectQuery
     * @param $expectedData
     * @param $expectedIds
     */
    public function selectQueryFromUpdate($query, $expectedSelectQuery, $expectedData, $expectedIds) {

        $this->wpdbStub->expects(new PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)->method("get_col")
            ->with($expectedSelectQuery)->will(new PHPUnit_Framework_MockObject_Stub_Return($expectedIds));
        $parsedQueryData = $this->sqlParser->parseQuery($query, self::$DbSchemaInfo, $this->wpdbStub);
        $this->assertEquals($expectedSelectQuery, $parsedQueryData->sqlQuery);
    }

    /**
     * @test
     * @dataProvider insertQueryParseTestDataProvider
     * @param $query
     * @param $expectedData
     */
    public function dataFromInsert($query, $expectedData) {

        $parsedQueryData = $this->sqlParser->parseQuery($query, self::$DbSchemaInfo, $this->wpdbStub);
        $parsedData = $parsedQueryData == null ? null : $parsedQueryData->data;
        $this->assertEquals($expectedData, $parsedData);
    }


    /**
     * @test
     * @dataProvider insertQueryParseTestDataProvider
     * @param $query
     * @param $expectedData
     * @param $expectedQueryType
     */
    public function detectNonStandardInsert($query, $expectedData, $expectedQueryType) {

        $parsedQueryData = $this->sqlParser->parseQuery($query, self::$DbSchemaInfo, $this->wpdbStub);
        $parsedQeryType = $parsedQueryData == null ? null : $parsedQueryData->queryType;
        $this->assertEquals($expectedQueryType, $parsedQeryType);
    }

    /**
     * @test
     * @dataProvider deleteQueryParseTestDataProvider
     * @param $query
     * @param $expectedSelectQuery
     * @param $testIds
     */
    public function selectQueryFromDelete($query, $expectedSelectQuery, $testIds) {
        $this->wpdbStub->expects(new PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)->method("get_col")
            ->with($expectedSelectQuery)->will(new PHPUnit_Framework_MockObject_Stub_Return($testIds));
        $parsedQueryData = $this->sqlParser->parseQuery($query, self::$DbSchemaInfo, $this->wpdbStub);
        $this->assertEquals($expectedSelectQuery, $parsedQueryData->sqlQuery);
    }


    public function deleteQueryParseTestDataProvider() {
        $testIds = [1, 3, 15];
        return array(
            array("DELETE o1 FROM `wp_options` AS o1 JOIN `wp_options` AS o2 ON o1.option_name=o2.option_name WHERE o2.option_id > o1.option_id",
                "SELECT option_name FROM `wp_options` AS o1 JOIN `wp_options` AS o2 ON o1.option_name=o2.option_name WHERE o2.option_id > o1.option_id",
                $testIds
            ),
            array("DELETE o1 FROM `wp_options` AS o1 JOIN `wp_options` AS o2 USING (`option_name`) WHERE o2.option_id > o1.option_id",
                "SELECT option_name FROM `wp_options` AS o1 JOIN `wp_options` AS o2 ON o1.option_name=o2.option_name WHERE o2.option_id > o1.option_id",
                $testIds
            ),
            array("DELETE FROM `wp_usermeta` WHERE meta_key IN ('key1', 'key2')",
                "SELECT umeta_id FROM `wp_usermeta` WHERE meta_key IN ('key1', 'key2')",
                $testIds
            ),
            array("DELETE FROM `wp_usermeta` WHERE meta_key = 'key1'",
                "SELECT umeta_id FROM `wp_usermeta` WHERE meta_key = 'key1'",
                $testIds
            )
        );
    }


    public function insertQueryParseTestDataProvider() {
        return array(
            array("INSERT INTO `wp_options` (`option_name`, `option_value`, `autoload`) VALUES ('name', 'value', 1) ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`), `autoload` = VALUES(`autoload`)",
                array(array("option_name" => "name", "option_value" => "value", "autoload" => "1")),
                ParsedQueryData::INSERT_UPDATE_QUERY
            ),
            array(
                "INSERT IGNORE INTO `wp_terms` (term_id, name, slug, term_group) VALUES (10, 'term name', 'term-name', 5) , (20, 'term another', 'term-another', 15)",
                null,
                null
            ),
            array(
                "INSERT INTO `wp_terms` (term_id, name, slug, term_group) VALUES (10, 'term name', 'term-name', 5)",
                array(array("term_id" => "10", "name" => "term name", "slug" => "term-name", "term_group" => "5")),
                ParsedQueryData::INSERT_QUERY
            ),
            array(
                "INSERT INTO `wp_terms` (term_id, name, slug, term_group) VALUES (10, 'term name', 'term-name', 5) , (20, 'term another', 'term-another', 15)",
                array(array("term_id" => "10", "name" => "term name", "slug" => "term-name", "term_group" => "5"),
                    array("term_id" => "20", "name" => "term another", "slug" => "term-another", "term_group" => "15")),
                ParsedQueryData::INSERT_QUERY
            ),
            array(
                "INSERT INTO `wp_terms` (term_id, date) VALUES (10, NOW())",
                array(array("term_id" => "10", "date" => "NOW")),
                ParsedQueryData::INSERT_QUERY
            )

        );
    }

    public function updateQueryParseTestDataProvider() {
        $testIds = [1, 3, 15];
        return array(
            array(
                "UPDATE  `wp_posts` SET post_modified = NOW() WHERE post_author = 'B'",
                "SELECT ID FROM `wp_posts` WHERE post_author = 'B'",
                array("post_modified" => "NOW()"),
                $testIds
            ),
            array("UPDATE `wp_posts` SET post_date = post_modified WHERE post_date = '0000-00-00 00:00:00'",
                "SELECT ID FROM `wp_posts` WHERE post_date = '0000-00-00 00:00:00'",
                array("post_date" => "post_modified"),
                $testIds
            ),
            array(
                "UPDATE `wp_options` SET option_value=REPLACE(option_value, 'wp-links/links-images/', 'wp-images/links/') WHERE option_name LIKE '%_' AND option_value LIKE '%s'",
                "SELECT option_name FROM `wp_options` WHERE option_name LIKE '%_' AND option_value LIKE '%s'",
                array("option_value" => ""),
                $testIds
            ),
            array(
                "UPDATE `wp_options` SET option_value=REPLACE(option_value, 'wp-links/links-images/', 'wp-images/links/'), option_abc = 'def' WHERE option_name LIKE '%A' AND option_value LIKE '%s'",
                "SELECT option_name FROM `wp_options` WHERE option_name LIKE '%A' AND option_value LIKE '%s'",
                array("option_value" => ""),
                $testIds
            ),
            array(
                "UPDATE `wp_posts` SET post_parent = '10', post_type='page' WHERE post_type = 'attachment' AND ID IN (" . join(',', $testIds) . ")",
                "SELECT ID FROM `wp_posts` WHERE post_type = 'attachment' AND ID IN (" . join(',', $testIds) . ")",
                array("post_parent" => "'10'", "post_type" => "'page'"),
                $testIds
            ),
            array(
                "UPDATE `wp_posts` SET post_date_gmt = DATE_ADD(post_date, INTERVAL '01:20' HOUR_MINUTE)",
                "SELECT ID FROM `wp_posts` WHERE 1=1",
                array("post_date_gmt" => "DATE_ADD(post_date"),
                $testIds
            ),
            array(
                "UPDATE  `wp_posts` SET post_author = 'A' WHERE post_author = 'B'",
                "SELECT ID FROM `wp_posts` WHERE post_author = 'B'",
                array("post_author" => "'A'"),
                $testIds
            )


        );
    }


}
