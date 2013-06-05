<?php

class UserMetaStorage extends SingleFileStorage implements EntityStorage {

    function __construct($file) {
        parent::__construct($file, 'user', 'ID');
    }

    function save($data, $restriction = array(), $id = 0) {
        $values = array_merge($data, $restriction);
        $data = array(
            'ID' => $values['user_id'],
            $values['meta_key'] => $values['meta_value']
        );

        $this->saveEntity($data, array($this, 'notifyOnChangeListeners'));
    }
}