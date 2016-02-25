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
    public $dirty;
    
    
    /**
    * @var string
    */
    public $queryType;
    
    
    public function __construct($queryType) {
       $this->queryType = $queryType; 
    }


}
