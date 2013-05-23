<?php

class IniSerializer {
    static function serialize($data, $hasSections = false) {
        $output = null;;
        if(!$hasSections) {
            $output = self::serializeData($data, 0);
        } else {
            $output = array();
            foreach($data as $sectionName => $section) {
                $output = array_merge($output, self::serializeSection($sectionName, $section, 0));
            }
        }
        return implode("\r\n", $output);
    }

    private static function serializeSection($sectionName, $data, $level) {
        $output = array();
        $output[] = str_repeat(" ", $level * 2) . "[" . $sectionName . "]";
        $output = array_merge($output, self::serializeData($data, $level + 1));
        return $output;
    }

    private static function serializeData($data, $level) {
        $output = array();
        $indentation = str_repeat(" ", $level * 2);
        foreach ($data as $key => $value) {
            if(is_array($value))
                $output = array_merge($output, self::serializeSection($key, $value, $level));
            else
                $output[] = $indentation . $key . " = " . (is_numeric($value) ? $value : '"' . $value . '"');
        }
        return $output;
    }

    static function deserialize($string) {
        return parse_ini_string($string);
    }
}