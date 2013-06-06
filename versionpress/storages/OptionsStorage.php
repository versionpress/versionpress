<?php

class OptionsStorage extends SingleFileStorage implements EntityStorage {

    protected $notSavedFields = array('option_id');

    function __construct($file) {
        parent::__construct($file, 'option', 'option_name');
    }

    public function shouldBeSaved($data) {
        $id = $data[$this->idColumnName];
        return !(substr($id, 0, 1) === '_' || $id === 'cron');
    }
}