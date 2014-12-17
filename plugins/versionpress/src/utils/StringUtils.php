<?php


namespace VersionPress\Utils;
use NStrings;

class StringUtils {

    /**
     * Converts given verb to past sense. E.g., "install" -> "installed",
     * "activate" -> "activated" etc.
     *
     * @param string $verb
     * @return string
     */
    public static function verbToPastTense($verb) {
        return $verb . (NStrings::endsWith($verb, "e") ? "d" : "ed");
    }

}
