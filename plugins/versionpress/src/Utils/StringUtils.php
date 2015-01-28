<?php


namespace VersionPress\Utils;
use Nette\Utils\Strings;

/**
 * Our string utils. Only adds what's not already provided by NStrings.
 *
 * @link http://doc.nette.org/en/2.2/strings
 */
class StringUtils {

    /**
     * Converts given verb to past sense. E.g., "install" -> "installed",
     * "activate" -> "activated" etc.
     *
     * @param string $verb
     * @return string
     */
    public static function verbToPastTense($verb) {
        return $verb . (Strings::endsWith($verb, "e") ? "d" : "ed");
    }

}
