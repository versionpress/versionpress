<?php

namespace VersionPress\Storages;

use VersionPress\ChangeInfos\OptionChangeInfo;
use VersionPress\Utils\IniSerializer;

class OptionsStorage extends SingleFileStorage {

    protected $notSavedFields = array('option_id');

    public function shouldBeSaved($data) {
        $blacklist = array(
            'cron',          // Cron, siteurl and home are specific for environment, so they're not saved, too.
            'home',
            'siteurl',
            'db_upgraded',
            'recently_edited',
            'auto_updater.lock',
            'can_compress_scripts',
            'auto_core_update_notified',
        );

        $id = $data[$this->entityInfo->idColumnName];
        return !($this->isTransientOption($id) || in_array($id, $blacklist));
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

    private function isTransientOption($id) {
        return substr($id, 0, 1) === '_'; // All transient options begin with underscore - there's no need to save them
    }
}