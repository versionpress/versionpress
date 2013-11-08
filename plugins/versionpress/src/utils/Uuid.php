<?php

final class Uuid {

    static function newUuid() {
        return self::generate('%04x%04x-%04x-%04x-%04x-%04x%04x%04x');
    }

    static function newUuidWithoutDelimiters() {
        return self::generate('%04x%04x%04x%04x%04x%04x%04x%04x');
    }

    // From: http://stackoverflow.com/questions/4049455/how-to-create-a-uuid-in-php-without-a-external-library
    private static function generate($formatString) {
        return strtoupper(sprintf($formatString,
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        ));
    }

    private function __construct() {
    }
}