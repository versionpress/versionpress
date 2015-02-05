<?php

namespace VersionPress\Utils;

/**
 * Serializes and deserializes data arrays into INI strings.
 *
 * Our INI format is a bit stricter than the generic INI format. Specifically, the following rules apply:
 *
 *  - Strings strictly use CRLF line endings (some implementations also allow LF)
 *  - Section names and keys are case sensitive (as opposed to Windows built-in implementation)
 *  - At least empty string is always serialized, e.g., `key = ""`, never `key = `
 *  - The key/value separator is always ` = ` (single space followed by equals sign followed by empty space)
 *
 * The serializer supports two kinds of data structures:
 *
 *  1. Flat - simple associative arrays
 *  2. Sectioned - associative arrays where key-value sets are grouped into sections
 *
 * Problematic cases:
 *
 *  - "\" (should be invalid, see http://3v4l.org/1DJ49)
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
    public static function serializeSectionedData($data) {
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
     * Serializes section - works recursively for subsections
     *
     * @param string $sectionName Something like "Section"
     * @param array $data
     * @param string $parentFullName Empty string or something ending with a dot, e.g. "ParentSection."
     * @return array Array of strings that will be lines in the output INI string
     */
    private static function serializeSection($sectionName, $data, $parentFullName = "") {

        $data = self::ensureCorrectOrder($data);

        $output = array();

        if (!self::containsOnlySubsections($data)) {
            $output[] = "[$parentFullName$sectionName]";
        }

        $output = array_merge($output, self::serializeData($data, $parentFullName . $sectionName . "."));

        // Add empty line after section. There could have been empty lines already generated from recursive
        // calls so just add it if it's necessary.
        if (end($output) !== "") {
            $output[] = "";
        }

        return $output;
    }

    /**
     * Returns true of the section data contains only subsections.
     *
     * @param $data
     * @return bool
     */
    private static function containsOnlySubsections($data) {

        // Let's keep the full-fledged algo here but there could also be a simpler one:
        // should there be a normal key-value pair in the data, it would be the first one
        // because if it followed after some subsection, it would belong to the subsection in the INI
        // format. So it must be first.

        foreach ($data as $key => $value) {
            if (!ArrayUtils::isAssociative($value)) {
                return false;
            }
        }

        return true;

    }

    private static function serializeData($data, $parentFullName) {
        $output = array();
        foreach ($data as $key => $value) {
            if ($key == '') continue;
            if (is_array($value)) {

                if (!ArrayUtils::isAssociative($value)) {

                    // Plain arrays are serialized as key[0] = val1, key[1] = val2

                    foreach ($value as $arrayKey => $arrayValue)
                        $output[] = self::serializeKeyValuePair($key . "[$arrayKey]", $arrayValue);

                } else {

                    // Associative arrays create subsections

                    $output = array_merge($output, self::serializeSection($key, $value, $parentFullName));
                }

            } else {
                $output[] = self::serializeKeyValuePair($key, $value);
            }
        }
        return $output;
    }

    private static function escapeString($str) {
        $str = str_replace('\\', '\\\\', $str); // single backslash to two, as in single-quoted PHP strings, see WP-289
        $str = str_replace('"', '\"', $str); // escape double quotes, must be done after backslashes
        return $str;
    }


    /**
     * Deserializes nested INI format into an array structure
     *
     * @param string $string Nested INI string
     * @return array Array structure corresponding to the nested INI format
     */
    public static function deserialize($string) {
        $string = self::eolWorkaround_addPlaceholders($string);
        $deserialized = parse_ini_string($string, true);
        $deserialized = self::eolWorkaround_removePlaceholders($deserialized);
        $deserialized = self::recursive_parse($deserialized);
        return $deserialized;
    }

    /**
     * PHP (Zend, not HHVM) has a bug that causes parse_ini_string() to fail when the line
     * ends with an escaped quote, like:
     *
     * ```
     * key = "start of some multiline value \"
     * continued here"
     * ```
     *
     * The workaround is to replace CR and LF chars inside the values (and ONLY inside the values)
     * with custom placeholders which will then be reverted back.
     *
     * @param string $iniString
     * @return mixed
     */
    private static function eolWorkaround_addPlaceholders($iniString) {

        // https://regex101.com/r/cJ6eN0/3
        $stringValueRegEx = "/\"(.*)(?<!\\\\)\"/sU";

        $iniString = preg_replace_callback($stringValueRegEx, function($matches) {
            return IniSerializer::getReplacedEolString($matches[0], "charsToPlaceholders");
        }, $iniString);

        return $iniString;
    }

    /**
     * @param $deserializedArray
     * @return array
     */
    private static function eolWorkaround_removePlaceholders($deserializedArray) {

        foreach ($deserializedArray as $key => $value) {
            if (is_array($value)) {
                $deserializedArray[$key] = self::eolWorkaround_removePlaceholders($value);
            } else if (is_string($value)) {
                $deserializedArray[$key] = self::getReplacedEolString($value, "placeholdersToChars");
            }
        }

        return $deserializedArray;

    }

    private static function getReplacedEolString($str, $direction) {

        $replacement = array(
            "\n" => "{{{EOL-LF}}}",
            "\r" => "{{{EOL-CR}}}",
        );

        $from = ($direction == "charsToPlaceholders") ? array_keys($replacement) : array_values($replacement);
        $to = ($direction == "charsToPlaceholders") ? array_values($replacement) : array_keys($replacement);

        return str_replace($from, $to, $str);

    }


    private static function outputToString($output) {
        return implode("\r\n", $output);
    }


    /**
     * Serializes key-value pair
     *
     * @param string $key
     * @param string|int|float $value String or a numeric value (number or string containing number)
     * @return string
     */
    private static function serializeKeyValuePair($key, $value) {
        return $key . " = " . (is_numeric($value) ? $value : '"' . self::escapeString($value) . '"');
    }

    /**
     * Simple key-values must appear before subsections for serialization to work correctly,
     * which this method ensures.
     *
     * @param array $data
     * @return array
     */
    private static function ensureCorrectOrder($data) {
        $keyValues = array();
        $subsections = array();
        foreach ($data as $key => $value) {
            if (ArrayUtils::isAssociative($value)) {
                $subsections[$key] = $value;
            } else {
                $keyValues[$key] = $value;
            }
        }

        return array_merge($keyValues, $subsections);
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
