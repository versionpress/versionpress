<?php

namespace VersionPress\Storages;

use VersionPress\ChangeInfos\TermChangeInfo;
use VersionPress\Utils\EntityUtils;

class TermStorage extends DirectoryStorage {

    protected function createChangeInfo($oldEntity, $newEntity, $action = null) {
        $diff = EntityUtils::getDiff($oldEntity, $newEntity);

        if ($oldEntity && isset($diff['name'])) {
            return new TermChangeInfo('rename', $newEntity['vp_id'], $newEntity['name'], 'term', $oldEntity['name']);
        }

        return new TermChangeInfo($action, $newEntity['vp_id'], $newEntity['name'], 'term');
    }
}