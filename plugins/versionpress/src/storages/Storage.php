<?php

abstract class Storage {

    abstract function save($data);
    abstract function delete($restriction);
    abstract function loadAll();
    abstract function saveAll($entities);
    abstract function shouldBeSaved($data);
    abstract function prepareStorage();
    abstract function getEntityFilename($id);

    /**
     * @var callable[]
     */
    private $onChangeListeners = array();

    function addChangeListener($callback) {
        $this->onChangeListeners[] = $callback;
    }

    protected function callOnChangeListeners(EntityChangeInfo $changeInfo) {
        foreach ($this->onChangeListeners as $onChangeListener) {
            call_user_func($onChangeListener, $changeInfo);
        }
    }
}