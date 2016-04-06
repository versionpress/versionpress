<?php

namespace VersionPress\Tests\Utils;


use VersionPress\Utils\ReferenceUtils;

class ReferenceUtilsTest extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function simpleNumericKey() {
        $pathInStructure = '[0]';
        $value = [0 => 'value'];

        $paths = ReferenceUtils::getMatchingPaths($value, $pathInStructure);

        $this->assertEquals([[0]], $paths);
    }

    /**
     * @test
     */
    public function nestedNumericKeys() {
        $pathInStructure = '[0][1]';
        $value = [0 => [1 => 'value']];

        $paths = ReferenceUtils::getMatchingPaths($value, $pathInStructure);

        $this->assertEquals([[0, 1]], $paths);
    }

    /**
     * @test
     */
    public function moreNestedNumericKeys() {
        $pathInStructure = '[0][1][2]';
        $value = [0 => [1 => [2 => 'value']]];

        $paths = ReferenceUtils::getMatchingPaths($value, $pathInStructure);

        $this->assertEquals([[0, 1, 2]], $paths);
    }

    /**
     * @test
     */
    public function combinedNumericAndStringKeys() {
        $pathInStructure = '[0]["some-key"][1]';
        $value = [0 => ['some-key' => [1 => 'value']]];

        $paths = ReferenceUtils::getMatchingPaths($value, $pathInStructure);

        $this->assertEquals([[0, 'some-key', 1]], $paths);
    }

    /**
     * @test
     */
    public function structureWithNotMatchingData() {
        $pathInStructure = '[0]["some-key"][1]';
        $value = [
            0 => ['some-key' => [1 => 'value']],
            1 => ['some-key' => [1 => 'value']]
        ];

        $paths = ReferenceUtils::getMatchingPaths($value, $pathInStructure);

        $this->assertEquals([[0, 'some-key', 1]], $paths);
    }

    /**
     * @test
     */
    public function simpleRegex() {
        $pathInStructure = '[/\d+/]';
        $value = [
            0 => 'value',
            1 => 'value',
        ];

        $paths = ReferenceUtils::getMatchingPaths($value, $pathInStructure);

        $this->assertEquals([[0], [1]], $paths);
    }

    /**
     * @test
     */
    public function simpleRegexWithNotMatchingData() {
        $pathInStructure = '[/\d+/]';
        $value = [
            0 => 'value',
            'string-key' => 'value',
            1 => 'value',
            'string-with-number-0' => 'value',
        ];

        $paths = ReferenceUtils::getMatchingPaths($value, $pathInStructure);

        $this->assertEquals([[0], [1]], $paths);
    }

    /**
     * @test
     */
    public function morePatternsWithNotMatchingData() {
        $pathInStructure = '[/\d+/][/some-.*/][/[0-9]+/]';
        $value = [
            0 => ['some-key' => [1 => 'value']],
            'string-key' => 'value',
            1 => ['some-other-key' => [1 => 'value']],
            2 => ['some-key' => 'value'],
        ];

        $paths = ReferenceUtils::getMatchingPaths($value, $pathInStructure);

        $this->assertEquals([[0, 'some-key', 1], [1, 'some-other-key', 1]], $paths);
    }
}
