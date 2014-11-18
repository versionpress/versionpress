<?php

class TermsStorage extends SingleFileStorage {

    function __construct($file) {
        parent::__construct($file, 'term', 'term_id');
    }

    protected function createChangeInfo($oldEntity, $newEntity, $action = null) {
        return new TermChangeInfo($action, $newEntity['vp_id'], $newEntity['name']);
    }
}