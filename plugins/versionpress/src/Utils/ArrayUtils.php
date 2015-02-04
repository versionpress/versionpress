<?php

namespace VersionPress\Utils;

class ArrayUtils {

    public static function parametrize($array) {
        $out = array();
        foreach ($array as $key => $value)
            $out[] = "$key=$value";
        return $out;
    }

    /**
     * Similar to `usort()` but maintains order of equal items (the algorithm is "stable").
     *
     * Adapted from http://stackoverflow.com/a/4353844/21728 / http://php.net/manual/en/function.usort.php#38827
     *
     * @param $array
     * @param string $cmp_function
     */
    public static function stablesort(&$array, $cmp_function = 'strcmp') {
        // Arrays of size < 2 require no action.
        if (count($array) < 2) return;
        // Split the array in half
        $halfway = count($array) / 2;
        $array1 = array_slice($array, 0, $halfway);
        $array2 = array_slice($array, $halfway);
        // Recurse to sort the two halves
        self::stablesort($array1, $cmp_function);
        self::stablesort($array2, $cmp_function);
        // If all of $array1 is <= all of $array2, just append them.
        if (call_user_func($cmp_function, end($array1), $array2[0]) < 1) {
            $array = array_merge($array1, $array2);
            return;
        }
        // Merge the two sorted arrays into a single sorted array
        $array = array();
        $ptr1 = $ptr2 = 0;
        while ($ptr1 < count($array1) && $ptr2 < count($array2)) {
            if (call_user_func($cmp_function, $array1[$ptr1], $array2[$ptr2]) < 1) {
                $array[] = $array1[$ptr1++];
            } else {
                $array[] = $array2[$ptr2++];
            }
        }
        // Merge the remainder
        while ($ptr1 < count($array1)) $array[] = $array1[$ptr1++];
        while ($ptr2 < count($array2)) $array[] = $array2[$ptr2++];
        return;
    }

    /**
     * Determines whether any element of an array satisfies a condition.
     *
     * @param array $array
     * @param callable $predicate
     * @return bool
     */
    public static function any($array, $predicate) {
        foreach ($array as $item) {
            if ($predicate($item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Takes a field name and two or more arrays. Returns value of given field from first array where is defined.
     *
     * @param $field
     * @param $array
     * @param $array,...
     * @return mixed Value of field or null if is not found.
     */
    public static function getFieldFromFirstWhereExists($field, $array, $_ = null) {
        $arrays = func_get_args();
        array_shift($arrays); // field

        foreach ($arrays as $array) {
            if (is_array($array) && isset($array[$field])) {
                return $array[$field];
            }
        }

        return null;
    }
}
