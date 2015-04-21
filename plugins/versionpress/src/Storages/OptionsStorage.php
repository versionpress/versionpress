<?php

namespace VersionPress\Storages;

use VersionPress\ChangeInfos\OptionChangeInfo;
use VersionPress\Utils\IniSerializer;

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
            || $id === 'recently_edited'
            || $id === 'can_compress_scripts');
    }

    protected function createChangeInfo($oldEntity, $newEntity, $action = null) {
        return new OptionChangeInfo($action, $newEntity[$this->entityInfo->idColumnName]);
    }

    protected function loadEntities() {
        if (is_file($this->file)) {
            $entities = IniSerializer::deserializeFlat(file_get_contents($this->file));

            foreach ($entities as $id => &$entity) {
                $entity[$this->entityInfo->vpidColumnName] = $id;
            }

            $this->entities = $entities;
        } else {
            $this->entities = array();
        }
    }
}