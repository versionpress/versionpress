<?php

namespace VersionPress\Cli;

use VersionPress\Utils\Process;

class VPCommandUtils {
    public static function runWpCliCommand($command, $subcommand, $args = array(), $cwd = null) {

        $cliCommand = "wp $command";

        if ($subcommand) {
            $cliCommand .= " $subcommand";
        }

        foreach ($args as $name => $value) {
            if (is_int($name)) { // positional argument
                $cliCommand .= " " . escapeshellarg($value);
            } elseif ($value !== null) {
                $cliCommand .= " --$name=" . escapeshellarg($value);
            } else {
                $cliCommand .= " --$name";
            }
        }

        return self::exec($cliCommand, $cwd);
    }

    /**
     * Executes a command, optionally in a specified working directory.
     *
     * @param string $command
     * @param string|null $cwd
     * @return Process
     */
    public static function exec($command, $cwd = null) {
        // Changing env variables for debugging
        // If we run another wp-cli command from our command, it breaks and never continues (with xdebug).
        // So we need to turn xdebug off for all "nested" commands.
        if (isset($_SERVER["XDEBUG_CONFIG"])) {
            $env = $_SERVER;
            unset($env["XDEBUG_CONFIG"]);
        } else {
            $env = null;
        }

        $process = new Process($command, $cwd, $env);
        $process->run();
        return $process;
    }

    /**
     * Asks user a question, offers an array of values and returns what he/she entered. The first value
     * in the `$values` array is the default one, used if the user just ENTERs the question or types
     * in some random value. The $assoc_args array may be given as the third parameter, in which case
	 * it behaves like in WP_CLI::confirm() and will return 'y' is such value is in $values,
     * or the default value.
     *
     * Inspired by WP_CLI::confirm() that can only do "yes" / "no" and cannot really return "no"
     * (it just exits in such case).
     *
     * @param string $question
     * @param array $values e.g. ["y", "n"] or ["1", "2", "3"]
     * @param array $assoc_args If $assoc_args contain 'yes' and $values contain 'y', it will be automatically answered.
     *   (Similar behavior to WP_CLI::confirm().)
     * @return string The answer
     */
    public static function cliQuestion($question, $values, $assoc_args = array()) {

        if (isset($assoc_args['yes'])) {
            return in_array('y', $values) ? 'y' : $values[0];
        }

        fwrite(STDOUT, $question . " [" . implode('/', $values) . "] ");
        $answer = trim(fgets(STDIN));

        if (!in_array($answer, $values)) {
            $answer = $values[0];
        }

        return $answer;
    }
}
