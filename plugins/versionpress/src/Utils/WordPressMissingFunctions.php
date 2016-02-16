<?php

namespace VersionPress\Utils;

class WordPressMissingFunctions {

    public static function getWpConfigPath() {
        $defaultWpConfigPath = realpath(ABSPATH . 'wp-config.php');
        $elevatedWpConfigPath = realpath(ABSPATH . '../wp-config.php');

        if (is_file($defaultWpConfigPath)) {
            return $defaultWpConfigPath;
        }

        return $elevatedWpConfigPath;
    }
}
