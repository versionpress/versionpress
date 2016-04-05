<?php

namespace VersionPress\Utils;

class Comparators {
    public static function equals($expected) {
        return function ($value) use ($expected) {
            return $value === $expected;
        };
    }
}
