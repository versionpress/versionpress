<?php

class OptionsStorage extends SingleFileStorage implements EntityStorage {

    protected $notSavedFields = array('option_id');

    function __construct($file) {
        parent::__construct($file, 'option', 'option_name');
    }

    public function shouldBeSaved($data) {
        $id = $data[$this->idColumnName];
        return !(substr($id, 0, 1) === '_' || $id === 'cron'); // With underscore begins all transient settings - there's no need to save them
                                                               // Cron is specific for environment, so it's not saved, too.
    }
}