<?php
namespace VersionPress\Database;

class Database {

    /**
     * @var \wpdb
     */
    private $wpdb;


    public function __construct($wpdb) {
        $this->wpdb = $wpdb;
    }

    /**
     * @see \wpdb::prefix
     * @return string
     */
    public function getTablePrefix() {
        return $this->wpdb->prefix;
    }

    /**
     * @see \wpdb::get_row()
     * @param $query
     * @return array|mixed|null|object|void
     */
    public function getRow($query) {
        return $this->wpdb->get_row($query);
    }

    /**
     * @see \wpdb::insert_id
     * @return int
     */
    public function getLastInsertedId() {
        return $this->wpdb->insert_id;
    }

    /**
     * @see \wpdb::get_col()
     * @param $query
     * @return array
     */
    public function getColumn($query) {
        return $this->wpdb->get_col($query);
    }

    /**
     * @see \wpdb::prepare()
     *
     * @param $statement
     * @param $where
     * @return false|null|string|void
     */
    public function prepareStatement($statement, $where) {
        return $this->wpdb->prepare($statement, $where);
    }

    /**
     * @see \wpdb::get_results()
     * @param $query
     * @param $outputFormat
     * @return array|object|null
     */
    public function getResults($query, $outputFormat) {
        return $this->wpdb->get_results($query, $outputFormat);
    }

    /**
     * @see \wpdb::postmeta
     * @return string
     */
    public function getPostmeta() {
        return $this->wpdb->postmeta;
    }

    /**
     * @see \wpdb::posts
     * @return string
     */
    public function getPosts() {
        return $this->wpdb->posts;
    }

    /**
     * @see \wpdb::options
     * @return string
     */
    public function getOptions() {
        return $this->wpdb->options;
    }

    /**
     * @see \wpdb::term_taxonomy
     * @return string
     */
    public function getTermTaxonomy() {
        return $this->wpdb->term_taxonomy;
    }

    /**
     * @see \wpdb::term_relationships
     * @return string
     */
    public function getTermRelationships() {
        return $this->wpdb->term_relationships;
    }

    /**
     * @see \wpdb::get_var()
     * @param $query
     * @return null|string
     */
    public function getVariable($query) {
        return $this->wpdb->get_var($query);
    }

    /**
     * @see \wpdb::query()
     * @param $query
     * @return false|int
     */
    public function query($query) {
        if (method_exists("wpdb", "vp_direct_query")) {
            return $this->wpdb->vp_direct_query($query);
        } else {
            return $this->wpdb->query($query);
        }
    }

    /**
     * @see \wpdb::_escape()
     * @param $data
     * @return array|string
     */
    public function escape($data) {
        return $this->wpdb->_escape($data);
    }

    /**
     * @see \wpdb::_real_escape()
     * @param $data
     * @return array|string
     */
    public function realEscape($data) {
        return $this->wpdb->_real_escape($data);
    }

    /**
     * @see \wpdb::tables()
     * @return array
     */
    public function tables() {
        return $this->wpdb->tables();
    }

    /**
     * @see \wpdb::update()
     * @param $table
     * @param $data
     * @param $where
     * @return false|int
     */
    public function update($table, $data, $where) {
        return $this->wpdb->update($table, $data, $where);
    }

}
