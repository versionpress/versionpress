<?php

define('ARRAY_MAP', 'ARRAY_MAP');

/**
 * Adds new output format - a map, where first DB row is used as key and second one as value
 */
class ExtendedWpdb extends wpdb {

    function __construct($dbuser, $dbpassword, $dbname, $dbhost) {
        global $table_prefix;
        parent::__construct($dbuser, $dbpassword, $dbname, $dbhost);
        $this->prefix = $table_prefix;
    }

    function get_row($query = null, $output = OBJECT, $y = 0) {
        if ($output === ARRAY_MAP) {
            $result = parent::get_row($query, ARRAY_N, $y);
            return array($result[0] => $result[1]);
        }
        return parent::get_row($query, $output, $y);
    }


    function get_results($query = null, $output = OBJECT) {
        if ($output === ARRAY_MAP) {
            $result = parent::get_results($query, $output = ARRAY_N);
            if (!$this->last_result) return $result;

            $map = array();
            foreach ($result as $row) {
                $map[$row[0]] = $row[1];
            }
            return $map;
        }

        return parent::get_results($query, $output);
    }
}