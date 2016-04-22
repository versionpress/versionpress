<?php

namespace VersionPress\Utils;

/**
 * Utility class to generate globally unique IDs, see the `newId()` method.
 */
final class IdUtil
{

    /**
     * Generates and returns a new ID that is suitable as a globally unique identifier
     * of any VersionPress entity (post, comment, user, etc.). The format is basically
     * an upper-case, delimiter-less UUID (GUID) version 4, e.g. `B3851EC74871452792B5EF5616BAE1E6`.
     *
     * See wiki docs for discussion about the chosen ID algorightm and format.
     *
     * @return string
     */
    public static function newId()
    {
        return self::newUuid('%04x%04x%04x%04x%04x%04x%04x%04x');
    }

    public static function getRegexMatchingId()
    {
        return '/([0-9A-F]{32})/';
    }

    /**
     * Inspired by http://php.net/manual/en/function.uniqid.php#94959
     *
     * @param $formatString
     * @return string
     */
    public static function newUuid($formatString = '%04x%04x-%04x-%04x-%04x-%04x%04x%04x')
    {
        return strtoupper(sprintf(
            $formatString,
            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        ));
    }

    private function __construct()
    {
    }
}
