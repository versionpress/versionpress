<?php

namespace VersionPress\Utils;

/**
 * Converts data arrays to and from INI strings
 *
 * Our INI format is a bit stricter than the generic INI format. Specifically, the following rules apply:
 *
 *  - CRLF line endings (as per standard INI but some implementations allow also LF)
 *  - Data structure must be sectioned, e.g. must be sometlink like array("Section" => array("key" => "value"))
 *    instead of array("key" => "value")
 *  - Sections must not be empty
 *  - At least empty string is always serialized, e.g., `key = ""`, never `key = `
 *
 * @package VersionPress\Utils
 */
class IniSerializer {


    /**
     * Serializes sectioned data array into an INI string
     *
     * @param array $data
     * @return string Nested INI format
     * @throws \Exception
     */
    public static function serialize($data) {
        $output = array();
        foreach ($data as $sectionName => $section) {
            if (!is_array($section)) {
                throw new \Exception("INI serializer only supports sectioned data");
            } else if (empty($section)) {
                throw new \Exception("Empty sections are not supported");
            }
            $output = array_merge($output, self::serializeSection($sectionName, $section, ""));
        }
        return self::outputToString($output);
    }

    /**
     * Serializes flat data (non-sectioned) into an INI string
     *
     * @param $data
     * @return string
     */
    public static function serializeFlatData($data) {
        return self::outputToString(self::serializeData($data, "", true));
    }

    private static function serializeSection($sectionName, $data, $parentFullName = "") {
        $output = array();
        $containsOnlyArrays = array_reduce($data, function ($prevState, $value) {
            return $prevState && is_array($value);
        }, true);

        if (!$containsOnlyArrays)
            $output[] = "[" . $parentFullName . $sectionName . "]";

        $output = array_merge($output, self::serializeData($data, $parentFullName . $sectionName . "."));

        // Add empty line after section. There could have been empty lines already generated from recursive
        // calls so just add it if it's necessary.
        if (end($output) !== "") {
            $output[] = "";
        }

        return $output;
    }

    private static function serializeData($data, $parentFullName, $flat = false) {
        $output = array();
        foreach ($data as $key => $value) {
            if ($key == '') continue;
            if (is_array($value))
                if ($flat) {
                    foreach ($value as $arrayKey => $arrayValue)
                        $output[] = self::formatEntry($key . "[$arrayKey]", $arrayValue);
                } else {
                    $output = array_merge($output, self::serializeSection($key, $value, $parentFullName));
                }
            else
                $output[] = self::formatEntry($key, $value);
        }
        return $output;
    }

    private static function escapeDoubleQuotes($str) {
        return str_replace('"', '\"', $str);
    }


    /**
     * Deserializes nested INI format into an array structure
     *
     * @param string $string Nested INI string
     * @return array Array structure corresponding to the nested INI format
     */
    public static function deserialize($string) {
        return self::recursive_parse(parse_ini_string($string, true));
    }

    private static function outputToString($output) {
        return implode("\r\n", $output);
    }

    private static function formatEntry($key, $value) {
        return $key . " = " . (is_numeric($value) ? $value : '"' . self::escapeDoubleQuotes($value) . '"');
    }

    /**
     * From http://stackoverflow.com/questions/3242175/parsing-an-advanced-ini-file-with-php
     * Creates hierarchical array from flat array with hierarchical keys.
     * E.g.:
     * Input:
     * [
     * "foo" => [
     *   "bar" => 1
     *   ],
     * "foo.something" => [
     *   "bar" => 2
     *   ]
     * ]
     *
     * Output:
     * [
     * "foo" => [
     *   "bar" => 1,
     *   "something" => [
     *     "bar => 2"
     *     ]
     *   ]
     * ]
     *
     * @param $array
     * @return array
     */
    private static function recursive_parse($array) {
        $returnArray = array();
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $array[$key] = self::recursive_parse($value);
                }
                $x = explode('.', $key);
                if (!empty($x[1])) {
                    $x = array_reverse($x, true);
                    if (isset($returnArray[$key])) {
                        unset($returnArray[$key]);
                    }
                    if (!isset($returnArray[$x[0]])) {
                        $returnArray[$x[0]] = array();
                    }
                    $first = true;
                    $b = null;
                    foreach ($x as $k => $v) {
                        if ($first === true) {
                            $b = $array[$key];
                            $first = false;
                        }
                        $b = array($v => $b);
                    }
                    $returnArray[$x[0]] = array_merge_recursive($returnArray[$x[0]], $b[$x[0]]);
                } else {
                    $returnArray[$key] = $array[$key];
                }
            }
        }
        return $returnArray;
    }
}
