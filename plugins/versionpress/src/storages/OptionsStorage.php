<?php

class OptionsStorage extends SingleFileStorage implements EntityStorage {

    protected $notSavedFields = array('option_id');

    function __construct($file) {
        parent::__construct($file, 'option', 'option_name');
        $this->idColumnName = 'option_name';
    }

    public function shouldBeSaved($data) {
        $id = $data[$this->idColumnName];
        return !(substr($id, 0, 1) === '_' // With underscore begins all transient settings - there's no need to save them
                || $id === 'cron'          // Cron, siteurl and home are specific for environment, so they're not saved, too.
                || $id === 'siteurl'
                || $id === 'home'
                || $id === 'db_upgraded'
                || $id === 'auto_updater.lock');
    }

    /**
     * @param $entity
     * @param $changeType
     * @return EntityChangeInfo
     */
    protected function createChangeInfo($entity, $changeType) {
        return new OptionChangeInfo($changeType, $entity[$this->idColumnName]);
    }
}