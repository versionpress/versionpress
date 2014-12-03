<?php

class TermsStorage extends SingleFileStorage {

    protected function createChangeInfo($oldEntity, $newEntity, $action = null) {
        $diff = EntityUtils::getDiff($oldEntity, $newEntity);

        if (isset($diff['name'])) {
            return new TermChangeInfo('rename', $newEntity['vp_id'], $newEntity['name'], $oldEntity['name']);
        }

        return new TermChangeInfo($action, $newEntity['vp_id'], $newEntity['name']);
    }
}