<?php

interface EntityStorage {
    function save($data, $restriction);
    function delete($restriction);
}