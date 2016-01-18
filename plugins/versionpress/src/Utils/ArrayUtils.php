<?php

namespace VersionPress\Utils;

class ArrayUtils {

    public static function parametrize($array) {
        $out = array();
        foreach ($array as $key => $value)
            if(empty($value))
                $out[] = "$key=''";
            else
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
     * Returns true if the array is associative. Associative being defined
     * as an array containing at least one string key.
     *
     * @param mixed $array Array is generally expected but any value may be provided (will return false)
     * @return bool
     */
    public static function isAssociative($array) {
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
    public static function any($array, $predicate) {
        foreach ($array as $item) {
            if ($predicate($item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Polyfil for array_column function.
     * Implementation from https://github.com/ramsey/array_column
     *
     * @param array[] $array
     * @param mixed $columnKey
     * @param mixed $indexKey
     * @return array
     */
    public static function column($array, $columnKey, $indexKey = null) {
        if (function_exists('array_column')) {
            return call_user_func_array('array_column', func_get_args());
        }

        // Using func_get_args() in order to check for proper number of
        // parameters and trigger errors exactly as the built-in array_column()
        // does in PHP 5.5.
        $argc = func_num_args();
        $params = func_get_args();
        if ($argc < 2) {
            trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
            return null;
        }
        if (!is_array($params[0])) {
            trigger_error('array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given', E_USER_WARNING);
            return null;
        }
        if (!is_int($params[1])
            && !is_float($params[1])
            && !is_string($params[1])
            && $params[1] !== null
            && !(is_object($params[1]) && method_exists($params[1], '__toString'))
        ) {
            trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
            return false;
        }
        if (isset($params[2])
            && !is_int($params[2])
            && !is_float($params[2])
            && !is_string($params[2])
            && !(is_object($params[2]) && method_exists($params[2], '__toString'))
        ) {
            trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
            return false;
        }
        $paramsInput = $params[0];
        $paramsColumnKey = ($params[1] !== null) ? (string) $params[1] : null;
        $paramsIndexKey = null;
        if (isset($params[2])) {
            if (is_float($params[2]) || is_int($params[2])) {
                $paramsIndexKey = (int) $params[2];
            } else {
                $paramsIndexKey = (string) $params[2];
            }
        }
        $resultArray = array();
        foreach ($paramsInput as $row) {
            $key = $value = null;
            $keySet = $valueSet = false;
            if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                $keySet = true;
                $key = (string) $row[$paramsIndexKey];
            }
            if ($paramsColumnKey === null) {
                $valueSet = true;
                $value = $row;
            } elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
                $valueSet = true;
                $value = $row[$paramsColumnKey];
            }
            if ($valueSet) {
                if ($keySet) {
                    $resultArray[$key] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }
        }
        return $resultArray;
    }

    /**
     * Transforms and groups data in array
     * $mapFn emits key and value. These values are grouped by their keys and passed into $reduceFn
     * which can transform the group and emit result.
     *
     * $mapFn    has signature ($item, $mapEmit) where $item is item from data and $mapEmit is emit function
     * $reduceFn has signature ($key, $items, $reduceEmit) where $key is the key the data are grouped by,
     *           $items is the group and $reduce emit is an emit function
     * $mapEmit has signature ($key, $value) where $key is the key the data are grouped by and $value is a transformed item
     * $reduceEmit has signature ($obj) where $obj is the transformed group
     *
     * @param $data
     * @param $mapFn
     * @param $reduceFn
     */
    public static function mapreduce($data, $mapFn, $reduceFn) {
        $mapResult = array();
        $reduceResult = array();

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
    public static function map($mapFn, $array) {
        return array_map(function ($key) use ($mapFn, $array) {
            return $mapFn($array[$key], $key);
        }, array_keys($array));
    }
}
