<?php

namespace VersionPress\Database;

class ParsedQueryData
{

    /**
     * Table name parsed from the source sql query. Usually prefixed e.g. `wp_posts`
     *
     * @var string
     */
    public $table;


    /**
     * VersionPress entity name resolved from `schema.yml` and $table
     *
     * @var string
     */
    public $entityName;

    /**
     * List of record Ids which are/will be affected by parsed query
     *
     * @var array
     */
    public $ids;

    /**
     * Data statements which will be applied to database when UPDATE or INSERT is performed.
     *
     * Example structure:
     *
     * [
     *      [ column => post_modified, value => NOW() ],
     *      [ column => another_column, value => 123 ]
     * ]
     * @var array;
     */
    public $data;


    /**
     * SELECT query created from provided source query (INSERT, UPDATE, DELETE)
     *
     * @var string
     */
    public $sqlQuery;

    /**
     * Id column name resolved from `schema.yml` and $table
     * @var string
     */
    public $idColumnName;

    /**
     * @var string Internal enumeration of queries which are currently supoorted by SqlQueryParser
     */
    public $queryType;

    const UPDATE_QUERY = 'UPDATE';
    const INSERT_QUERY = 'INSERT';
    const DELETE_QUERY = 'DELETE';
    const INSERT_IGNORE_QUERY = 'INSERT_IGNORE';
    const INSERT_UPDATE_QUERY = 'INSERT_UPDATE';


    public function __construct($queryType)
    {
        $this->queryType = $queryType;
    }
}
