<?php

class UserStorage extends SingleFileStorage implements EntityStorage {

    function __construct($file) {
        parent::__construct($file, 'user', 'ID');
    }
}