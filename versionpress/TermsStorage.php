<?php

class TermsStorage extends SingleFileStorage implements EntityStorage {

    protected $savedFields = array('name', 'slug', 'term_group', 'taxonomy', 'description', 'parent');

    function __construct($file) {
        parent::__construct($file, 'term', 'term_id');
    }
}