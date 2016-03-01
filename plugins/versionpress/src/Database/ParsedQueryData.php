<?php

namespace VersionPress\Database;


class ParsedQueryData {

    /**
     * @var string
     */
    public $table;

    /**
     * @var array
     */
    public $ids;

    /**
     * @var array;
     */
    public $data;


    /**
     * @var array
     */
    public $where;

    /**
     * @var string
     */
    public $query;

    /**
     * @var string
     */
    public $originalQuery;

    /**
     * @var boolean
     */
    public $usesSqlFunctions;
    
    
    public $idColumn;
    
    /**
    * @var string
    */
    public $queryType;

    const UPDATE_QUERY = 'UPDATE';
    const INSERT_QUERY = 'INSERT';
    const DELETE_QUERY = 'DELETE';
    const INSERT_IGNORE_QUERY = 'INSERT_IGNORE';
    
    
    public function __construct($queryType) {
       $this->queryType = $queryType; 
    }


}
