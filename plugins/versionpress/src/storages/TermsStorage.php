<?php

class TermsStorage extends SingleFileStorage implements EntityStorage {

    function __construct($file) {
        parent::__construct($file, 'term', 'term_id');
    }

    /**
     * @param $entity
     * @param $changeType
     * @return EntityChangeInfo
     */
    protected function createChangeInfo($entity, $changeType) {
        // TODO: Implement createChangeInfo() method.
    }
}