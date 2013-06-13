<?php

interface EntityStorage {
    function save($data, $restriction = array(), $id = 0);
    function delete($restriction);
    function loadAll();
    function saveAll($entities);
    function addChangeListener($callback);
    function shouldBeSaved($data);
    function prepareStorage();
}