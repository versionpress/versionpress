<?php

namespace VersionPress\Tests\Utils;

use VersionPress\Utils\ReferenceUtils;

class ReferenceUtilsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function simpleNumericKey()
    {
        $pathInStructure = '[0]';
        $value = serialize([0 => 'value']);

        $paths = ReferenceUtils::getMatchingPathsInSerializedData($value, $pathInStructure);

        $this->assertEquals([[[0]]], $paths);
    }

    /**
     * @test
     */
    public function nestedNumericKeys()
    {
        $pathInStructure = '[0][1]';
        $value = serialize([0 => [1 => 'value']]);

        $paths = ReferenceUtils::getMatchingPathsInSerializedData($value, $pathInStructure);

        $this->assertEquals([[[0, 1]]], $paths);
    }

    /**
     * @test
     */
    public function moreNestedNumericKeys()
    {
        $pathInStructure = '[0][1][2]';
        $value = serialize([0 => [1 => [2 => 'value']]]);

        $paths = ReferenceUtils::getMatchingPathsInSerializedData($value, $pathInStructure);

        $this->assertEquals([[[0, 1, 2]]], $paths);
    }

    /**
     * @test
     */
    public function combinedNumericAndStringKeys()
    {
        $pathInStructure = '[0]["some-key"][1]';
        $value = serialize([0 => ['some-key' => [1 => 'value']]]);

        $paths = ReferenceUtils::getMatchingPathsInSerializedData($value, $pathInStructure);

        $this->assertEquals([[[0, 'some-key', 1]]], $paths);
    }

    /**
     * @test
     */
    public function structureWithNotMatchingData()
    {
        $pathInStructure = '[0]["some-key"][1]';
        $value = serialize([
            0 => ['some-key' => [1 => 'value']],
            1 => ['some-key' => [1 => 'value']]
        ]);

        $paths = ReferenceUtils::getMatchingPathsInSerializedData($value, $pathInStructure);

        $this->assertEquals([[[0, 'some-key', 1]]], $paths);
    }

    /**
     * @test
     */
    public function simpleRegex()
    {
        $pathInStructure = '[/\d+/]';
        $value = serialize([
            0 => 'value',
            1 => 'value',
        ]);

        $paths = ReferenceUtils::getMatchingPathsInSerializedData($value, $pathInStructure);

        $this->assertEquals([[[0]], [[1]]], $paths);
    }

    /**
     * @test
     */
    public function simpleRegexWithNotMatchingData()
    {
        $pathInStructure = '[/\d+/]';
        $value = serialize([
            0 => 'value',
            'string-key' => 'value',
            1 => 'value',
            'string-with-number-0' => 'value',
        ]);

        $paths = ReferenceUtils::getMatchingPathsInSerializedData($value, $pathInStructure);

        $this->assertEquals([[[0]], [[1]]], $paths);
    }

    /**
     * @test
     */
    public function morePatternsWithNotMatchingData()
    {
        $pathInStructure = '[/\d+/][/some-.*/][/[0-9]+/]';
        $value = serialize([
            0 => ['some-key' => [1 => 'value']],
            'string-key' => 'value',
            1 => ['some-other-key' => [1 => 'value']],
            2 => ['some-key' => 'value'],
        ]);

        $paths = ReferenceUtils::getMatchingPathsInSerializedData($value, $pathInStructure);

        $this->assertEquals([[[0, 'some-key', 1]], [[1, 'some-other-key', 1]]], $paths);
    }

    /**
     * @test
     */
    public function nestedSerializedData()
    {
        $pathInStructure = '[0]..[0]';
        $value = serialize([serialize(['value'])]);

        $paths = ReferenceUtils::getMatchingPathsInSerializedData($value, $pathInStructure);

        $this->assertEquals([[[0], [0]]], $paths);
    }

    /**
     * @test
     */
    public function regexPathsInNestedSerializedData()
    {
        $pathInStructure = '[/\d+/]..[/prefix_\d+/]';
        $value = serialize([serialize(['prefix_0' => 'value', 'prefix_1' => 'value']), serialize(['prefix_2' => 'value', 'prefix_3' => 'value'])]);

        $paths = ReferenceUtils::getMatchingPathsInSerializedData($value, $pathInStructure);

        $expectedPaths = [
            [[0], ['prefix_0']],
            [[0], ['prefix_1']],
            [[1], ['prefix_2']],
            [[1], ['prefix_3']],
        ];
        $this->assertEquals($expectedPaths, $paths);
    }

    /**
     * @test
     */
    public function regexPathsInNestedSerializedDataWithMissingKeys()
    {
        $pathInStructure = '[/\d+/]..[/prefix_\d+/]';
        $value = serialize([serialize(['prefix_0' => 'value', 'prefix_1' => 'value']), serialize(['different_key' => 'value'])]);

        $paths = ReferenceUtils::getMatchingPathsInSerializedData($value, $pathInStructure);

        $expectedPaths = [
            [[0], ['prefix_0']],
            [[0], ['prefix_1']],
        ];
        $this->assertEquals($expectedPaths, $paths);
    }

    /**
     * @test
     */
    public function regexPathsInNestedSerializedDataWithExtraKeys()
    {
        $pathInStructure = '[/\d+/]..[/prefix_\d+/]';
        $value = serialize([serialize(['prefix_0' => 'value', 'prefix_1' => 'value']), serialize(['prefix_2' => 'value', 'prefix_3' => 'value', 'different_key' => 'value'])]);

        $paths = ReferenceUtils::getMatchingPathsInSerializedData($value, $pathInStructure);

        $expectedPaths = [
            [[0], ['prefix_0']],
            [[0], ['prefix_1']],
            [[1], ['prefix_2']],
            [[1], ['prefix_3']],
        ];
        $this->assertEquals($expectedPaths, $paths);
    }
}
