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

    public static function setUpBeforeClass() {
        self::$DbSchemaInfo = new DbSchemaInfo(__DIR__ . '/../../src/Database/wordpress-schema.neon', 'wp_', PHP_INT_MAX);
    }

    public function setup() {
        $this->wpdbStub = $this->getMockBuilder('\wpdb')->disableOriginalConstructor()->getMock();
    }


    /**
     * @test
     * @dataProvider updateQueryParseTestDataProvider
     */
    public function expectedWhereClausesAreOk($query, $select, $data, $where, $dirty) {
        $this->wpdbStub->expects(new PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)->method("get_col");


        /** @var ParsedQueryData $parsedQueryData */
        $parsedQueryData = SqlQueryParser::parseQuery($query, self::$DbSchemaInfo, $this->wpdbStub);
        $this->assertEquals($where, $parsedQueryData->where);

    }


    /**
     * @test
     * @dataProvider updateQueryParseTestDataProvider
     */
    public function expectedDataToSetAreOk($query, $select, $data, $where, $dirty) {
        $this->wpdbStub->expects(new PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)->method("get_col");


        /** @var ParsedQueryData $parsedQueryData */
        $parsedQueryData = SqlQueryParser::parseQuery($query, self::$DbSchemaInfo, $this->wpdbStub);
        $this->assertEquals($data, $parsedQueryData->data);


    }


    /**
     * @test
     * @dataProvider updateQueryParseTestDataProvider
     */
    public function expectedDirtyAreDirty($query, $select, $data, $where, $dirty) {

        $this->wpdbStub->expects(new PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)->method("get_col");


        /** @var ParsedQueryData $parsedQueryData */
        $parsedQueryData = SqlQueryParser::parseQuery($query, self::$DbSchemaInfo, $this->wpdbStub);
        $this->assertEquals($dirty, $parsedQueryData->dirty);

    }


    /**
     * @test
     * @dataProvider updateQueryParseTestDataProvider
     */
    public function expectedSelectQueriesAreOk($query, $select, $data, $where, $dirty) {

        $this->wpdbStub->expects(new PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)->method("get_col")
            ->with($select)->will(new PHPUnit_Framework_MockObject_Stub_Return(true));
        /** @var ParsedQueryData $parsedQueryData */
        $parsedQueryData = SqlQueryParser::parseQuery($query, self::$DbSchemaInfo, $this->wpdbStub);
        //print_r($parsedQueryData);
        $this->assertEquals($select, $parsedQueryData->query);

    }

    public function updateQueryParseTestDataProvider() {
        $testIds = [1, 3, 15];
        return array(
            array(
                "UPDATE `wp_options` SET option_value=REPLACE(option_value, 'wp-links/links-images/', 'wp-images/links/') WHERE option_name LIKE '%_' AND option_value LIKE '%s'",
                "SELECT option_name FROM `wp_options` WHERE option_name LIKE '%_' AND option_value LIKE '%s'",
                array(array("column" => "option_value", "value" => "")),
                array("option_name LIKE '%_'", "option_value LIKE '%s'"),
                1
            ),
            array(
                "UPDATE `wp_options` SET option_value=REPLACE(option_value, 'wp-links/links-images/', 'wp-images/links/'), option_abc = 'def' WHERE option_name LIKE '%A' AND option_value LIKE '%s'",
                "SELECT option_name FROM `wp_options` WHERE option_name LIKE '%A' AND option_value LIKE '%s'",
                array(array("column" => "option_value", "value" => "")),
                array("option_name LIKE '%A'", "option_value LIKE '%s'"),
                1
            ),
            array(
                "UPDATE `wp_posts` SET post_parent = '10', post_type='page' WHERE post_type = 'attachment' AND ID IN (" . join(',', $testIds) . ")",
                "SELECT ID FROM `wp_posts` WHERE post_type = 'attachment' AND ID IN (" . join(',', $testIds) . ")",
                array(array("column" => "post_parent", "value" => "'10'"), array("column" => "post_type", "value" => "'page'")),
                array("post_type = 'attachment'", "ID IN (" . join(',', $testIds) . ")"),
                0
            ),
            array(
                "UPDATE `wp_posts` SET post_date_gmt = DATE_ADD(post_date, INTERVAL '01:20' HOUR_MINUTE)",
                "SELECT ID FROM `wp_posts` WHERE 1=1",
                array(array("column" => "post_date_gmt", "value" => "DATE_ADD(post_date")),
                array("1=1"),
                1
            ),
            array(
                "UPDATE  `wp_posts` SET post_author = 'A' WHERE post_author = 'B'",
                "SELECT ID FROM `wp_posts` WHERE post_author = 'B'",
                array(array("column" => "post_author", "value" => "'A'")),
                array("post_author = 'B'"),
                0
            )


        );
    }


}
