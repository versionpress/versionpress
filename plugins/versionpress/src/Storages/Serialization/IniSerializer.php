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
class IniSerializer
{

    private static $sanitizedChars = [
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
    ];

    private static $numberMarker = '<<<VP-Number>>>';

    private static $nullMarker = '<<<VP-Null>>>>';

    private static $nullValue = '<null>';

    /**
     * Serializes sectioned data array into an INI string
     *
     * @param array $data
     * @return string Nested INI format
     * @throws \Exception
     */
    public static function serialize($data)
    {
        $output = [];
        foreach ($data as $sectionName => $section) {
            if (!is_array($section)) {
                throw new \Exception("INI serializer only supports sectioned data");
            } else {
                if (empty($section)) {
                    throw new \Exception("Empty sections are not supported");
                }
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
    private static function serializeSection($sectionName, $data)
    {
        $output = [];
        $output[] = "[$sectionName]";
        $output = array_merge($output, self::serializeData($data));

        // Add empty line after section. There could have been empty lines already generated from recursive
        // calls so just add it if it's necessary.
        if (end($output) !== "") {
            $output[] = "";
        }

        return $output;
    }

    private static function serializeData($data)
    {
        $output = [];
        foreach ($data as $key => $value) {
            if ($key == '') {
                continue;
            }
            if (is_array($value)) {
                foreach ($value as $arrayKey => $arrayValue) {
                    $output[] = self::serializeKeyValuePair($key . "[$arrayKey]", $arrayValue);
                }
            } elseif (StringUtils::isSerializedValue($value)) {
                $lines = SerializedDataToIniConverter::toIniLines($key, $value);
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
    private static function escapeString($str)
    {
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
    private static function unescapeString($str)
    {
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
    public static function deserialize($string)
    {
//        $string = self::eolWorkaround_addPlaceholders($string);

        $string = self::preserveNumbers($string);
        $string = self::preserveNULLs($string);
        $deserialized = [];

        $lines = explode("\r\n", $string);
        $actualSection = "";

        foreach ($lines as $line) {

            if ($line[0] == "[" && $endIdx = strpos($line, "]")) {
                $actualSection = substr($line, 1, $endIdx - 1);
                $deserialized[$actualSection] = [];
                continue;
            }

            if (Strings::length($line) == 0) {
                continue;
            }

            if (!strpos($line, '=')) {
                continue;
            }

            $splitRow = explode(" = ", $line, 2);

            $key = $splitRow[0];
            $value = $splitRow[1];
            $value = self::sanitizeSectionsAndKeys_addPlaceholders($value);
            if (Strings::startsWith($value, "\"")) {
                $value = StringUtils::substringFromTo($value, 1, Strings::length($value));
            }
            if (Strings::endsWith($value, "\"")) {
                $value = StringUtils::substringFromTo($value, 0, Strings::length($value) - 1);
            }

            $value = self::restoreTypesOfValue($value);


            if (Strings::contains($key, "[")) {
                $keyAtSection = StringUtils::substringFromTo($key, 0, strpos($key, '['));
                $ketAtSub = StringUtils::substringFromTo($key, strpos($key, "[") + 1, strpos($key, ']'));
                $deserialized[$actualSection][$keyAtSection][$ketAtSub] = $value;
            } else {
                unset($ketAtSub, $keyAtSection);
                $deserialized[$actualSection][$key] = $value;
            }

        }
        print_r($deserialized);

//
//        $deserialized = parse_ini_string($string, true, INI_SCANNER_RAW);
        $deserialized = self::sanitizeSectionsAndKeys_removePlaceholders($deserialized);
//        $deserialized = self::eolWorkaround_removePlaceholders($deserialized);
        $deserialized = self::restorePhpSerializedData($deserialized);
        $deserialized = self::expandArrays($deserialized);
//        return $deserialized;

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
    private static function eolWorkaround_addPlaceholders($iniString) // @codingStandardsIgnoreLine
    {
        $prefaceString = ' = "'; // sequence of characters before string value

        $position = 0;
        $length = strlen($iniString);
        $result = "";

        // Read the string char by char
        while ($position < $length) {
            $nextPrefacePos = strpos($iniString, $prefaceString, $position);

            if ($nextPrefacePos === false) {
                // There are no more string values
                // Just append the rest of the string and we're done
                $result .= substr($iniString, $position);
                break;
            }

            // Append everything from the end of last string value to the start of another
            $result .= StringUtils::substringFromTo($iniString, $position, $nextPrefacePos + strlen($prefaceString));

            // Set position to the start of the string value
            $position = $nextPrefacePos + strlen($prefaceString);
            $stringBeginPos = $stringEndPos = $position;
            $isEndOfString = false;

            while (!$isEndOfString) {
                if ($iniString[$position] === '\\') {
                    // Found escaped character
                    // Skip this one and the following one
                    $position += 2;
                    continue;
                } else {
                    if ($iniString[$position] === '"') {
                        // This is it. Unescaped double-quote means that the string value ends here.
                        $isEndOfString = true;
                        $stringEndPos = $position;
                    } else {
                        // Regular character. Boooring - move along.
                        $position += 1;
                    }
                }
            }

            // OK. We have the beginning and the end. Let's replace all line-endings with placeholders.
            $value = StringUtils::substringFromTo($iniString, $stringBeginPos, $stringEndPos);
            $result .= self::getReplacedEolString($value, 'charsToPlaceholders');
        }

        return $result;
    }

    /**
     * @param $deserializedArray
     * @return array
     */
    private static function eolWorkaround_removePlaceholders($deserializedArray) // @codingStandardsIgnoreLine
    {

        foreach ($deserializedArray as $key => $value) {
            if (is_array($value)) {
                $deserializedArray[$key] = self::eolWorkaround_removePlaceholders($value);
            } else {
                if (is_string($value)) {
                    $deserializedArray[$key] = self::getReplacedEolString($value, "placeholdersToChars");
                }
            }
        }

        return $deserializedArray;
    }

    private static function getReplacedEolString($str, $direction)
    {

        $replacement = [
            "\n" => "<<<[EOL-LF]>>>",
            "\r" => "<<<[EOL-CR]>>>",
        ];

        $from = ($direction == "charsToPlaceholders") ? array_keys($replacement) : array_values($replacement);
        $to = ($direction == "charsToPlaceholders") ? array_values($replacement) : array_keys($replacement);

        return str_replace($from, $to, $str);
    }


    private static function outputToString($output)
    {
        return implode("\n", $output);
    }

    private static function preserveNumbers($iniString)
    {
        // https://regex101.com/r/pH5hE9/3
        $re = "/= -?\\d+(?:\\.\\d+)?\\r?\\n/m";
        return preg_replace_callback($re, function ($m) {
            return str_replace('= ', '= ' . self::$numberMarker, $m[0]);
        }, $iniString);
    }

    private static function preserveNULLs($iniString)
    {
        https://regex101.com/r/tF2wK2/1
        $re = "/= (<null>)\\r?\\n/m";
        return preg_replace_callback($re, function ($m) {
            return str_replace('= ', '= ' . self::$nullMarker, $m[0]);
        }, $iniString);
    }

    /**
     * Serializes key-value pair
     *
     * @param string $key
     * @param string|int|float $value String or a numeric value (number or string containing number)
     * @return string
     */
    private static function serializeKeyValuePair($key, $value)
    {
        if (is_null($value)) {
            $value = self::$nullValue;
        } elseif (is_string($value)) {
            $value = '"' . self::escapeString($value) . '"';
        }
        return $key . " = " . $value;
    }

    private static function sanitizeSectionsAndKeys_addPlaceholders($string) // @codingStandardsIgnoreLine
    {
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

    private static function sanitizeSectionsAndKeys_removePlaceholders($deserialized) // @codingStandardsIgnoreLine
    {
        $result = [];
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
    private static function expandArrays($deserialized)
    {
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

    private static function restoreTypesOfValues($deserialized)
    {
        $result = [];
        foreach ($deserialized as $key => $value) {
            if (is_array($value)) {
                $result[$key] = self::restoreTypesOfValues($value);
            } else {
                $result[$key] = self::restoreTypesOfValue($value);
            }
        }
        return $result;
    }


    private static function restoreTypesOfValue($value)
    {
        if (Strings::startsWith($value, self::$numberMarker)) {
            // strip the marker and convert to number
            return str_replace(self::$numberMarker, '', $value) + 0;
        } elseif (Strings::startsWith($value, self::$nullMarker)) {
            return null;
        } else {
            return self::unescapeString($value);
        }
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
    private static function restorePhpSerializedData($deserialized)
    {
        $keysToRestore = [];

        foreach ($deserialized as $key => $value) {
            if (is_array($value)) {
                $deserialized[$key] = self::restorePhpSerializedData($value);
            } else {
                if (Strings::startsWith($value, SerializedDataToIniConverter::SERIALIZED_MARKER)) {
                    $keysToRestore[] = $key;
                }
            }
        }

        foreach ($keysToRestore as $key) {
            $relatedKeys = array_filter($deserialized, function ($maybeRelatedKey) use ($key) {
                return Strings::startsWith($maybeRelatedKey, $key);
            }, ARRAY_FILTER_USE_KEY);

            $keysToUnset = $relatedKeys;
            // unset all related lines except the first one (it will be replaced without changing position in the array)
            unset($keysToUnset[$key]);

            $deserialized = array_diff_key($deserialized, $keysToUnset);
            $deserialized[$key] = SerializedDataToIniConverter::fromIniLines($key, $relatedKeys);
        }

        return $deserialized;
    }
}
