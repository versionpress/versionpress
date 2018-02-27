<?php

namespace VersionPress\Utils;

class ProcessUtils
{

    /**
     * `escapeshellarg()` reimplementation that is custom-coded for both Linux and Windows. This fixes some
     * bugs on Windows (quotes not escaped properly) and allows you to force the escaping type
     * using the `$os` parameter should you need to.
     *
     * @param string $arg
     * @param string|null $os "windows", "linux" or null to auto-detect
     * @return mixed|string
     */
    public static function escapeshellarg($arg, $os = null)
    {

        if (!$os) {
            $os = DIRECTORY_SEPARATOR == '\\' ? "windows" : "linux";
        }

        if ($os == "windows") {
            return self::escapeshellargWindows($arg);
        } else {
            return self::escapeshellargLinux($arg);
        }
    }

    /**
     * Linux shell escaping from Drush:
     * http://drupalcontrib.org/api/drupal/contributions!drush!includes!exec.inc/function/_drush_escapeshellarg_linux/7
     *
     * @param $arg
     * @return mixed|string
     */
    private static function escapeshellargLinux($arg)
    {
        // For single quotes existing in the string, we will "exit"
        // single-quote mode, add a \' and then "re-enter"
        // single-quote mode.  The result of this is that
        // 'quote' becomes '\''quote'\''
        $arg = preg_replace('/\'/', '\'\\\'\'', $arg);

        // Replace "\t", "\n", "\r", "\0", "\x0B" with a whitespace.
        // Note that this replacement makes Drush's escapeshellarg work differently
        // than the built-in escapeshellarg in PHP on Linux, as these characters
        // usually are NOT replaced. However, this was done deliberately to be more
        // conservative when running _drush_escapeshellarg_linux on Windows
        // (this can happen when generating a command to run on a remote Linux server.)
        $arg = str_replace(["\t", "\n", "\r", "\0", "\x0B"], ' ', $arg);

        // Add surrounding quotes.
        $arg = "'" . $arg . "'";

        return $arg;
    }

    /**
     * Windows shell escaping from Drush:
     *
     * @codingStandardsIgnoreLine
     * http://drupalcontrib.org/api/drupal/contributions!drush!includes!exec.inc/function/_drush_escapeshellarg_windows/7
     *
     * @param $arg
     * @return mixed|string
     */
    private static function escapeshellargWindows($arg)
    {
        // Double up existing backslashes
        $arg = preg_replace('/(?!\\\\\/)\\\/', '\\\\\\\\', $arg);

        // Double up double quotes
        $arg = preg_replace('/"/', '""', $arg);

        // Double up percents.
        $arg = preg_replace('/%/', '%%', $arg);

        // Add surrounding quotes.
        $arg = '"' . $arg . '"';

        return $arg;
    }
}
