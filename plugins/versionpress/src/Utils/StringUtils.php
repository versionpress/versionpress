<?php


namespace VersionPress\Utils;

use Nette\Utils\Strings;

/**
 * Our string utils. Only adds what's not already provided by NStrings.
 *
 * @link http://doc.nette.org/en/2.2/strings
 */
class StringUtils {

    /**
     * Converts given verb to past sense. E.g., "install" -> "installed",
     * "activate" -> "activated" etc.
     *
     * @param string $verb
     * @return string
     */
    public static function verbToPastTense($verb) {
        return $verb . (Strings::endsWith($verb, "e") ? "d" : "ed");
    }

    /**
     * Converts LF string to CRLF string
     *
     * @param string $str LF line endings
     * @return string CRLF line endings
     */
    public static function crlfize($str) {
        return str_replace("\n", "\r\n", str_replace("\r\n", "\n", $str));
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
    public static function pluralize($string) {
        $plural = array(
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
        );

        $irregular = array(
            'move' => 'moves',
            'foot' => 'feet',
            'goose' => 'geese',
            'sex' => 'sexes',
            'child' => 'children',
            'man' => 'men',
            'tooth' => 'teeth',
            'person' => 'people',
            'valve' => 'valves'
        );

        $uncountable = array(
            'sheep',
            'fish',
            'deer',
            'series',
            'species',
            'money',
            'rice',
            'information',
            'equipment'
        );

        // save some time in the case that singular and plural are the same
        if (in_array(strtolower($string), $uncountable))
            return $string;


        // check for irregular singular forms
        foreach ($irregular as $pattern => $result) {
            $pattern = '/' . $pattern . '$/i';

            if (preg_match($pattern, $string))
                return preg_replace($pattern, $result, $string);
        }

        // check for matches using regular expressions
        foreach ($plural as $pattern => $result) {
            if (preg_match($pattern, $string))
                return preg_replace($pattern, $result, $string);
        }

        return $string;
    }
}
