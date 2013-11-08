<?php

class Arrays {
    public static function parametrize($array) {
        $out = array();
        foreach ($array as $key => $value)
            $out[] = "$key=$value";
        return $out;
    }
}