<?php

namespace VersionPress\Storages\Serialization;

use Nette\Utils\Strings;
use VersionPress\Utils\StringUtils;

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

    private static $sanitizedChars = array(
        "[" => "<<<lbrac>>>",
        "]" => "<<<rbrac>>>",
        "\"" => "<<<dblquot>>>",
        "'" => "<<<quot>>>",
        ";" => "<<<semicol>>>",
        "$" => "<<<string>>>",
        "&" => "<<<amp>>>",
        "~" => "<<<tilde>>>",
        "^" => "<<<power>>>",
        "!" => "<<<exclmark>>>",
        "(" => "<<<lparent>>>",
        ")" => "<<<rparent>>>",
        "{" => "<<<lcurly>>>",
        "}" => "<<<rcurly>>>",
        "|" => "<<<pipe>>>",
        "\t" => "<<<tab>>>",
        "=" => "<<<eq>>>",
    );

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
            $output = array_merge($output, self::serializeSection($sectionName, $section));
        }
        return self::outputToString($output);
    }

    /**
     * Serializes section - works recursively for subsections
     *
     * @param string $sectionName Something like "Section"
     * @param array $data
     * @return array Array of strings that will be lines in the output INI string
     */
    private static function serializeSection($sectionName, $data) {
        $output = array();
        $output[] = "[$sectionName]";
        $output = array_merge($output, self::serializeData($data));

        // Add empty line after section. There could have been empty lines already generated from recursive
        // calls so just add it if it's necessary.
        if (end($output) !== "") {
            $output[] = "";
        }

        return $output;
    }

    private static function serializeData($data) {
        $output = array();
        foreach ($data as $key => $value) {
            if ($key == '') continue;
            if (is_array($value)) {
                foreach ($value as $arrayKey => $arrayValue) {
                    $output[] = self::serializeKeyValuePair($key . "[$arrayKey]", $arrayValue);
                }
            } elseif (StringUtils::isSerializedValue($value)) {
                $serializedDataToIniConverter = new SerializedDataToIniConverter();
                $lines = $serializedDataToIniConverter->toIniLines($key, $value);
                $output = array_merge($output, $lines);
            } else {
                $output[] = self::serializeKeyValuePair($key, $value);
            }
        }
        return $output;
    }

    /**
     * Called when serializing data into an INI string. The only character that needs special handling is a double
     * quotation mark, see e.g. WP-284. All others are fine since using INI_SCANNER_RAW (WP-458).
     *
     * @param $str
     * @return mixed
     */
    private static function escapeString($str) {
        $str = str_replace('\\', '\\\\', $str);
        $str = str_replace('"', '\\"', $str);
        return $str;
    }

    /**
     * The opposite to escapeString(), called when INI strings are restored back to arrays. Again,
     * the only char that needs special handling is the double quotation mark.
     *
     * @param $str
     * @return mixed
     */
    private static function unescapeString($str) {
        $str = str_replace('\\\\', '\\', $str);
        $str = str_replace('\\"', '"', $str);
        return $str;
    }


    /**
     * Deserializes INI format into an array structure
     *
     * @param string $string INI string
     * @return array Array structure corresponding to the INI format
     */
    public static function deserialize($string) {
        $string = self::eolWorkaround_addPlaceholders($string);
        $string = self::sanitizeSectionsAndKeys_addPlaceholders($string);
        $deserialized = parse_ini_string($string, true, INI_SCANNER_RAW);
        $deserialized = self::restoreTypesOfValues($deserialized);
        $deserialized = self::sanitizeSectionsAndKeys_removePlaceholders($deserialized);
        $deserialized = self::restorePhpSerializedData($deserialized);
        $deserialized = self::expandArrays($deserialized);
        $deserialized = self::eolWorkaround_removePlaceholders($deserialized);
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

        // https://regex101.com/r/cJ6eN0/6
        $stringValueRegEx = "/ = \"((?:[^\"\\\\]|\\\\.)*)\"/sU";

        $iniString = preg_replace_callback($stringValueRegEx, array('VersionPress\Storages\Serialization\IniSerializer', 'replace_eol_callback'), $iniString);

        return $iniString;
    }

    private static function replace_eol_callback($matches) {
        return self::getReplacedEolString($matches[0], "charsToPlaceholders");
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
            "\n" => "<<<[EOL-LF]>>>",
            "\r" => "<<<[EOL-CR]>>>",
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

    private static function sanitizeSectionsAndKeys_addPlaceholders($string) {
        $sanitizedChars = self::$sanitizedChars;
        // Replace brackets in section names
        // https://regex101.com/r/bT2nO7/2
        $string = preg_replace_callback("/^\\[(.*)\\]/m", function ($match) use ($sanitizedChars) {
            $sectionWithPlaceholders = strtr($match[1], $sanitizedChars);
            return "[$sectionWithPlaceholders]";
        }, $string);

        // Replace brackets and quotes in keys
        // https://regex101.com/r/iD5oO0/3
        $string = preg_replace_callback("/^(.*?) = /m", function ($match) use ($sanitizedChars) {
            $keyWithPlaceholders = strtr($match[1], $sanitizedChars);
            return $keyWithPlaceholders . (isset($match[2]) ? $match[2] : "") . " = ";
        }, $string);

        return $string;
    }

    private static function sanitizeSectionsAndKeys_removePlaceholders($deserialized) {
        $result = array();
        foreach ($deserialized as $key => $value) {
            $key = strtr($key, array_flip(self::$sanitizedChars));
            if (is_array($value)) {
                $result[$key] = self::sanitizeSectionsAndKeys_removePlaceholders($value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * Transforms e.g.
     * [
     *  'key' => 'value',
     *  'another_key[0]' => 'value',
     *  'another_key[1]' => 'another value',
     * ]
     *
     * to
     * [
     *  'key' => 'value',
     *  'another_key' => [
     *    0 => 'value',
     *    1 => 'another value',
     *  ]
     * ]
     *
     *
     * @param $deserialized
     * @return array
     */
    private static function expandArrays($deserialized) {
        $dataWithExpandedArrays = [];

        foreach ($deserialized as $key => $value) {
            if (is_array($value)) {
                $value = self::expandArrays($value);
            }

            // https://regex101.com/r/bA6uD2/3
            if (preg_match("/(.*)\\[([^]]+)\\]$/", $key, $matches)) {
                $originalKey = $matches[1];
                $subkey = $matches[2];
                $dataWithExpandedArrays[$originalKey][$subkey] = $value;
            } else {
                $dataWithExpandedArrays[$key] = $value;
            }
        }

        return $dataWithExpandedArrays;
    }

    private static function restoreTypesOfValues($deserialized) {
        $result = array();
        foreach ($deserialized as $key => $value) {
            if (is_array($value)) {
                $result[$key] = self::restoreTypesOfValues($value);
            } else if (is_numeric($value)) {
                $result[$key] = $value + 0;
            } else if ($value === 'true' || $value === 'false') {
                $result[$key] = $value === 'true';
            } else {
                $result[$key] = self::unescapeString($value);
            }
        }
        return $result;
    }

    /**
     * Converts all PHP-serialized data in the INI (multiple lines, made for easy merging)
     * to the original PHP-serialized strings.
     *
     * Example:
     *
     * [
     *  'some_option' => [
     *    'option_value' => '<<<serialized>>> <array>',
     *    'option_value[0]' = 'some serialized value',
     *    'autoload' => 1,
     *  ]
     * ]
     *
     *
     * is converted to
     *
     * [
     *  'some_option' => [
     *    'option_value' => 'a:1:{i:0;s:21:"some serialized value";}',
     *    'autoload' => 1,
     * ]
     *
     *
     *
     * @param $deserialized
     * @return array
     */
    private static function restorePhpSerializedData($deserialized) {
        $keysToRestore = [];

        foreach ($deserialized as $key => $value) {
            if (is_array($value)) {
                $deserialized[$key] = self::restorePhpSerializedData($value);
            } else if (Strings::startsWith($value, SerializedDataToIniConverter::SERIALIZED_MARKER)) {
                $keysToRestore[] = $key;
            }
        }

        $serializedDataToIniConverter = new SerializedDataToIniConverter(SerializedDataToIniConverter::SERIALIZED_MARKER);
        foreach ($keysToRestore as $key) {
            $relatedKeys = array_filter($deserialized, function ($maybeRelatedKey) use ($key) {
                return Strings::startsWith($maybeRelatedKey, $key);
            }, ARRAY_FILTER_USE_KEY);

            $deserialized = array_diff_key($deserialized, $relatedKeys);
            $deserialized[$key] = $serializedDataToIniConverter->fromIniLines($key, $relatedKeys);
        }

        return $deserialized;
    }
}
