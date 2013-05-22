<?php

interface EntityStorage {
    function save($data, $restriction = array());
    function delete($restriction);
    function loadAll();
    function saveAll($entities);
    function addChangeListener($callback);
}