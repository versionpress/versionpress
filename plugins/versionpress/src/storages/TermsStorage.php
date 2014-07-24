<?php

class TermsStorage extends SingleFileStorage implements EntityStorage {

    function __construct($file) {
        parent::__construct($file, 'term', 'term_id');
    }

    /**
     * @param $entityId
     * @param $changeType
     * @return EntityChangeInfo
     */
    protected function createChangeInfo($entityId, $changeType) {
        // TODO: Implement createChangeInfo() method.
    }
}