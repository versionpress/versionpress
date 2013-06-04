<?php

class TermsStorage extends SingleFileStorage implements EntityStorage {

    protected $savedFields = array('name', 'slug', 'term_group', 'vp_id');

    function __construct($file) {
        parent::__construct($file, 'term', 'term_id');
    }
}