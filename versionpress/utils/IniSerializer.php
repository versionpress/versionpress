<?php

class IniSerializer {

    static function serialize($data) {
        $output = array();
        foreach ($data as $sectionName => $section) {
            $output = array_merge($output, self::serializeSection($sectionName, $section, ""));
        }
        return self::outputToString($output);
    }

    static function serializeFlatData($data) {
        return self::outputToString(self::serializeData($data, "", true));
    }

    private static function serializeSection($sectionName, $data, $parentFullName = "") {
        $output = array();
        $containsOnlyArrays = array_reduce($data, function ($prevState, $value) {
            return $prevState && is_array($value);
        }, true);

        if(!$containsOnlyArrays)
            $output[] = "[" . $parentFullName . $sectionName . "]";

        $output = array_merge($output, self::serializeData($data, $parentFullName . $sectionName . "."));
        return $output;
    }

    private static function serializeData($data, $parentFullName, $flat = false) {
        $output = array();
        $indentation = "  ";
        foreach ($data as $key => $value) {
            if (is_array($value))
                if ($flat)
                    foreach ($value as $arrayKey => $arrayValue)
                        $output[] = self::formatEntry($indentation, $key . "[$arrayKey]", $arrayValue);
                else
                    $output = array_merge($output, self::serializeSection($key, $value, $parentFullName));

            else
                $output[] = self::formatEntry($indentation, $key, $value);
        }
        return $output;
    }

    private static function escapeDoubleQuotes($str) {
        return str_replace('"', '\"', $str);
    }

    static function deserialize($string) {
        return self::recursive_parse(parse_ini_string($string, true));
    }

    private static function outputToString($output) {
        return implode("\r\n", $output);
    }

    private static function formatEntry($indentation, $key, $value) {
        return $indentation . $key . " = " . (is_numeric($value) ? $value : '"' . self::escapeDoubleQuotes($value) . '"');
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