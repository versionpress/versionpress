<?php

interface EntityStorage {
    function save($data, $restriction = array());
    function delete($restriction);
    function loadAll();
    function saveAll($posts);
    function addChangeListener($callback);
}