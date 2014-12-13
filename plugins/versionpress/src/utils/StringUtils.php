<?php


class StringUtils {

    /**
     * Converts given verb to past sense. E.g., "install" -> "installed",
     * "activate" -> "activated" etc.
     *
     * @param string $verb
     * @return string
     */
    public static function verbToPastSense($verb){
        return $verb . (NStrings::endsWith($verb, "e") ? "d" : "ed");
    }

    /**
     * Capitalizes the given string, e.g. "example" -> "Example",
     * "lazy dog" -> "Lazy Dog" etc.
     *
     * @param string $str
     * @return string
     */
    public static function capitalize($str) {
        return NStrings::capitalize($str);
    }
    
}
