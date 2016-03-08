<?php

namespace VersionPress\Database;


class ParsedQueryData {

    /**
     * @var string
     */
    public $table;


    /**
     * @var string
     */
    public $entityName;

    /**
     * @var array
     */
    public $ids;

    /**
     * @var array;
     */
    public $data;


    /**
     * @var array;
     */
    public $parentIds;

    /**
     * @var string
     */
    public $sqlQuery;


    public $idColumnName;

    /**
     * @var string
     */
    public $queryType;

    const UPDATE_QUERY = 'UPDATE';
    const INSERT_QUERY = 'INSERT';
    const DELETE_QUERY = 'DELETE';
    const INSERT_IGNORE_QUERY = 'INSERT_IGNORE';
    const INSERT_UPDATE_QUERY = 'INSERT_UPDATE';


    public function __construct($queryType) {
        $this->queryType = $queryType;
    }


}
