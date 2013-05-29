<?php

class TermsStorage extends SingleFileStorage implements EntityStorage {

    protected $savedFields = array('name', 'slug', 'term_group', 'taxonomy', 'description', 'parent', 'vp_id', 'vp_parent_id', 'vp_term_id');

    function __construct($file) {
        parent::__construct($file, 'term', 'term_id');
    }
}