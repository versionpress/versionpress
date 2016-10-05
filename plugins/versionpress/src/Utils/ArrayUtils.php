<?php

namespace VersionPress\Utils;

class ArrayUtils
{

    public static function parametrize($array)
    {
        $out = [];
        foreach ($array as $key => $value) {
            if (empty($value)) {
                $out[] = "$key=''";
            } else {
                $out[] = "$key=$value";
            }
        }
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
    public static function stablesort(&$array, $cmp_function = 'strcmp')
    {
        // Arrays of size < 2 require no action.
        if (count($array) < 2) {
            return;
        }
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
        $array = [];
        $ptr1 = $ptr2 = 0;
        while ($ptr1 < count($array1) && $ptr2 < count($array2)) {
            if (call_user_func($cmp_function, $array1[$ptr1], $array2[$ptr2]) < 1) {
                $array[] = $array1[$ptr1++];
            } else {
                $array[] = $array2[$ptr2++];
            }
        }
        // Merge the remainder
        while ($ptr1 < count($array1)) {
            $array[] = $array1[$ptr1++];
        }
        while ($ptr2 < count($array2)) {
            $array[] = $array2[$ptr2++];
        }
        return;
    }

    /**
     * Returns true if the array is associative. Associative being defined
     * as an array containing at least one string key.
     *
     * @param mixed $array Array is generally expected but any value may be provided (will return false)
     * @return bool
     */
    public static function isAssociative($array)
    {
        if (!is_array($array)) {
            return false;
        }
        return (bool)count(array_filter(array_keys($array), 'is_string'));
    }

    /**
     * Determines whether any element of an array satisfies a condition.
     *
     * @param array $array
     * @param callable $predicate
     * @return bool
     */
    public static function any($array, $predicate)
    {
        foreach ($array as $key => $item) {
            if ($predicate($item, $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determines whether all elements of an array satisfy a condition.
     *
     * @param array $array
     * @param callable $predicate
     * @return bool
     */
    public static function all($array, $predicate)
    {
        foreach ($array as $key => $item) {
            if (!$predicate($item, $key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Transforms and groups data in array
     * $mapFn emits key and value. These values are grouped by their keys and passed into $reduceFn
     * which can transform the group and emit result.
     *
     * $mapFn    has signature ($item, $mapEmit) where $item is item from data and $mapEmit is emit function
     * $reduceFn has signature ($key, $items, $reduceEmit) where $key is the key the data are grouped by,
     *           $items is the group and $reduce emit is an emit function
     * $mapEmit has signature ($key, $value) where $key is the key the data are grouped by and $value is
     *          a transformed item
     * $reduceEmit has signature ($obj) where $obj is the transformed group
     *
     * @param $data
     * @param $mapFn
     * @param $reduceFn
     * @return array
     */
    public static function mapreduce($data, $mapFn, $reduceFn)
    {
        $mapResult = [];
        $reduceResult = [];

        $mapEmit = function ($key, $value) use (&$mapResult) {
            $mapResult[$key][] = $value;
        };

        $reduceEmit = function ($obj) use (&$reduceResult) {
            $reduceResult[] = $obj;
        };

        foreach ($data as $item) {
            $mapFn($item, $mapEmit);
        }

        foreach ($mapResult as $key => $value) {
            $reduceFn($key, $mapResult[$key], $reduceEmit);
        }

        return $reduceResult;
    }

    /**
     * Extends array_map function with keys.
     * The $mapFn takes two params - $value and $key.
     *
     * @param callable $mapFn
     * @param array $array
     * @return array
     */
    public static function map($mapFn, $array)
    {
        return array_map(function ($key) use ($mapFn, $array) {
            return $mapFn($array[$key], $key);
        }, array_keys($array));
    }

    /**
     * Removes duplicate values from an array.
     * Uses custom function to map the values to a comparable value.
     * It preserves keys.
     *
     * @param array $array
     * @param callable|null $fn
     * @return array
     */
    public static function unique($array, $fn = null)
    {
        $mapped = $fn ? array_map($fn, $array) : $array;
        $uniqueMapped = array_unique($mapped, SORT_REGULAR);
        return array_intersect_key($array, $uniqueMapped);
    }
}
