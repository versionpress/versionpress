<?php

class MirroringDatabase extends wpdb {

    /**
     * @var Mirror
     */
    private $mirror;

    function __construct($dbUser, $dbPassword, $dbName, $dbHost, Mirror $mirror) {
        parent::__construct($dbUser, $dbPassword, $dbName, $dbHost);
        $this->mirror = $mirror;
    }

    function insert($table, $data, $format = null) {
        $result = parent::insert($table, $data, $format);
        if(!isset($data['ID']))
            $data['ID'] = mysql_insert_id($this->dbh);
        $this->mirror->save($table, $data);
        return $result;
    }

    function update($table, $data, $where, $format = null, $where_format = null) {
        $result = parent::update($table, $data, $where, $format, $where_format);
        if(!isset($data['ID']))
            $data['ID'] = $where['ID'];
        $this->mirror->save($table, $data, $where);
        return $result;
    }

    function delete($table, $where, $where_format = null) {
        $result = parent::delete($table, $where, $where_format);
        $this->mirror->delete($table, $where);
        return $result;
    }
}