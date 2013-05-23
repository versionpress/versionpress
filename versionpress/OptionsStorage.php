<?php

class OptionsStorage extends ObservableStorage implements EntityStorage {

    private $options;

    private $file;

    function __construct($file) {
        $this->file = $file;
    }

    function save($data, $restriction = array(), $id = 0) {
        if (!isset($data['option_name']))
            $data['option_name'] = $restriction['option_name'];
        $this->saveOption($data, array($this, 'notifyOnChangeListeners'));
    }

    function delete($restriction) {
        $optionName = $restriction['option_name'];

        if(!$this->shouldBeSaved($optionName))
            return;

        $this->loadOptions();
        unset($this->options[$optionName]);
        $this->saveOptions();
    }

    function loadAll() {
        $this->loadOptions();
        return $this->options;
    }

    function saveAll($options) {
        foreach ($options as $option) {
            $this->saveOption($option);
        }
    }

    private function saveOption($data, $callback = null) {
        $optionName = $data['option_name'];
        if(!$this->shouldBeSaved($optionName))
            return;

        $this->loadOptions();
        $isNewOption = !isset($this->options[$optionName]);

        if($isNewOption) {
            $this->options[$optionName] = array();
        }
        $originalOptions = $this->options;

        $this->updateOption($optionName, $data);

        if($this->options != $originalOptions) {
            $this->saveOptions();

            if (is_callable($callback))
            $callback($optionName, $isNewOption);
        }
    }

    private function updateOption($optionName, $data) {
        $originalValues = $this->options[$optionName];
        static $fieldsToUpdate = array('option_value', 'autoload');

        foreach($fieldsToUpdate as $field)
            $this->options[$optionName][$field] = isset($data[$field]) ? $data[$field] : $originalValues[$field];
    }

    private function loadOptions() {
        if (is_file($this->file))
            $this->options = parse_ini_file($this->file, true);
        else
            $this->options = array();
    }

    private function saveOptions() {
        $options = IniSerializer::serialize($this->options);
        file_put_contents($this->file, $options);
    }

    private function notifyOnChangeListeners($optionName, $isNewOption) {
        $changeInfo = new ChangeInfo();
        $changeInfo->entityType = 'option';
        $changeInfo->entityId = $optionName;
        $changeInfo->type = $isNewOption ? 'create' : 'edit';

        $this->callOnChangeListeners($changeInfo);
    }

    private function shouldBeSaved($optionName) {
        return substr($optionName,0, 1) !== '_' || $optionName === 'cron';
    }
}