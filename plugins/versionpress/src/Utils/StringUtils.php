<?php


namespace VersionPress\Utils;

use Nette\Utils\Strings;

/**
 * Our string utils. Only adds what's not already provided by NStrings.
 *
 * @link http://doc.nette.org/en/2.2/strings
 */
class StringUtils
{

    /**
     * Converts given verb to past sense. E.g., "install" -> "installed",
     * "activate" -> "activated" etc.
     *
     * @param string $verb
     * @return string
     */
    public static function verbToPastTense($verb)
    {
        return $verb . (Strings::endsWith($verb, "e") ? "d" : "ed");
    }

    /**
     * Ensures LF line endings in a string
     *
     * @param string $str LF or CRLF line endings
     * @return string LF line endings
     */
    public static function ensureLf($str)
    {
        return str_replace("\r\n", "\n", $str);
    }

    /**
     * Converts given noun to plural form.
     * Note: It's very, very simplified!
     *
     * From: https://gist.github.com/tbrianjones/ba0460cc1d55f357e00b
     *
     * @param string $string
     * @return string
     */
    public static function pluralize($string)
    {
        $plural = [
            '/(quiz)$/i' => "$1zes",
            '/^(ox)$/i' => "$1en",
            '/([m|l])ouse$/i' => "$1ice",
            '/(matr|vert|ind)ix|ex$/i' => "$1ices",
            '/(x|ch|ss|sh)$/i' => "$1es",
            '/([^aeiouy]|qu)y$/i' => "$1ies",
            '/(hive)$/i' => "$1s",
            '/(?:([^f])fe|([lr])f)$/i' => "$1$2ves",
            '/(shea|lea|loa|thie)f$/i' => "$1ves",
            '/sis$/i' => "ses",
            '/([ti])um$/i' => "$1a",
            '/(tomat|potat|ech|her|vet)o$/i' => "$1oes",
            '/(bu)s$/i' => "$1ses",
            '/(alias)$/i' => "$1es",
            '/(octop)us$/i' => "$1i",
            '/(ax|test)is$/i' => "$1es",
            '/(us)$/i' => "$1es",
            '/s$/i' => "s",
            '/$/' => "s"
        ];

        $irregular = [
            'move' => 'moves',
            'foot' => 'feet',
            'goose' => 'geese',
            'sex' => 'sexes',
            'child' => 'children',
            'man' => 'men',
            'tooth' => 'teeth',
            'person' => 'people',
            'valve' => 'valves'
        ];

        $uncountable = [
            'sheep',
            'fish',
            'deer',
            'series',
            'species',
            'money',
            'rice',
            'information',
            'equipment',
            'meta',
        ];

        // save some time in the case that singular and plural are the same
        if (in_array(strtolower($string), $uncountable)) {
            return $string;
        }


        // check for irregular singular forms
        foreach ($irregular as $pattern => $result) {
            $pattern = '/' . $pattern . '$/i';

            if (preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }

        // check for matches using regular expressions
        foreach ($plural as $pattern => $result) {
            if (preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }

        return $string;
    }

    /**
     * Fills template string placeholders with variables provided in $variables array.
     * Placeholders pattern: {{variable-name}}
     *
     * @param array $variables associative array with keys as variable names.
     * @param string $templateString template string which contains placeholders for variables to be expanded.
     * @return string templateString with expanded variable placeholders
     */
    public static function fillTemplateString($variables, $templateString)
    {
        $search = array_map(function ($var) {
            return sprintf('{{%s}}', $var);
        }, array_keys($variables));
        $replace = array_values($variables);
        return str_replace($search, $replace, $templateString);
    }

    /**
     * Returns true if string is a serialized value (object, array, primitive types, ...).
     *
     * @param $value
     * @return bool
     */
    public static function isSerializedValue($value)
    {
        if (!is_string($value)) {
            return false;
        }
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        $test = @unserialize(($value)); // it throws an error and returns false if $value is not a serialized object
        return $test !== false || $value === 'b:0;';
    }

    /**
     * Replaces the first occurence.
     *
     * @param string $needle
     * @param string $replace
     * @param string $haystack
     * @return string
     */
    public static function replaceFirst($needle, $replace, $haystack)
    {
        $needlePosition = strpos($haystack, $needle);
        if ($needlePosition === false) {
            return $haystack;
        }

        return substr_replace($haystack, $replace, $needlePosition, strlen($needle));
    }

    /**
     * An alternative to the built-in PHP function `substr`.
     * The `substr` function needs the length of substring. This method takes bounds from-to.
     *
     * @param string $str
     * @param int $from
     * @param int $to
     * @return string
     */
    public static function substringFromTo($str, $from, $to)
    {
        return substr($str, $from, $to - $from);
    }
}
