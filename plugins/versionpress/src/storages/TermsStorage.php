<?php

class TermsStorage extends SingleFileStorage {

    protected function createChangeInfo($oldEntity, $newEntity, $action = null) {
        return new TermChangeInfo($action, $newEntity['vp_id'], $newEntity['name']);
    }
}