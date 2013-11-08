<?php

interface EntityStorage {
    function save($data);
    function delete($restriction);
    function loadAll();
    function saveAll($entities);
    function addChangeListener($callback);
    function shouldBeSaved($data);
    function prepareStorage();
}