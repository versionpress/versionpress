<?php

namespace VersionPress\Storages;

use VersionPress\ChangeInfos\OptionChangeInfo;

class OptionsStorage extends SingleFileStorage {

    protected $notSavedFields = array('option_id');

    public function shouldBeSaved($data) {
        $id = $data[$this->entityInfo->idColumnName];
        return !(substr($id, 0, 1) === '_' // With underscore begins all transient settings - there's no need to save them
            || $id === 'cron'          // Cron, siteurl and home are specific for environment, so they're not saved, too.
            || $id === 'siteurl'
            || $id === 'home'
            || $id === 'db_upgraded'
            || $id === 'auto_updater.lock'
            || $id === 'recently_edited');
    }

    protected function createChangeInfo($oldEntity, $newEntity, $action = null) {
        return new OptionChangeInfo($action, $newEntity[$this->entityInfo->idColumnName]);
    }
}