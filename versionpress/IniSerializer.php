<?php

class IniSerializer {
    static function serialize($data) {
        $output = array();
        foreach ($data as $key => $value) {
            $output[] = "$key = " . (is_numeric($value) ? $value : '"' . $value . '"');
        }
        return implode("\r\n", $output);
    }

    static function deserialize($string) {
        return parse_ini_string($string);
    }
}