<?php

namespace VersionPress\Tests\Utils;


class ArrayAsserter {
    /**
     * Asserts that one array is identical to another, but the order of keys does not matter.
     *
     * @param $expected
     * @param $actual
     */
    public static function assertSimilar($expected, $actual) {
        if (!is_array($expected) || !is_array($actual)) {
            throw new \PHPUnit_Framework_AssertionFailedError('Only arrays can be compared');
        }

        ksort($expected);
        ksort($actual);

        $constraint = new \PHPUnit_Framework_Constraint_IsIdentical($expected);
        $constraint->evaluate($actual);
    }
}