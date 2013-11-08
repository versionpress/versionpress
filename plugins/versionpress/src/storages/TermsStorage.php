<?php

class TermsStorage extends SingleFileStorage implements EntityStorage {

    function __construct($file) {
        parent::__construct($file, 'term', 'term_id');
    }
}