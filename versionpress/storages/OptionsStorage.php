<?php

class OptionsStorage extends SingleFileStorage implements EntityStorage {

    protected $notSavedFields = array('option_id');

    function __construct($file) {
        parent::__construct($file, 'option', 'option_name');
    }

    protected function shouldBeSaved(array $data) {
        $id = $data[$this->idColumnName];
        return substr($id,0, 1) !== '_' && $id !== 'cron';
    }
}