<?php

class MirroringDatabase extends wpdb {

    function __construct($dbUser, $dbPassword, $dbName, $dbHost) {
        parent::__construct($dbUser, $dbPassword, $dbName, $dbHost);
    }

    function insert($table, $data, $format = null) {
        return parent::insert($table, $data, $format);
    }

    function update($table, $data, $where, $format = null, $where_format = null) {
        return parent::update($table, $data, $where, $format, $where_format);
    }

    function delete($table, $where, $where_format = null) {
        return parent::delete($table, $where, $where_format);
    }
}