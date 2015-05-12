<?php

namespace VersionPress\Cli;

use Symfony\Component\Process\Process;

class VPCommandUtils {
    public static function runWpCliCommand($command, $subcommand, $args = array()) {

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

        return self::exec($cliCommand);
    }

    public static function exec($command) {
        // Changing env variables for debugging
        // If we run another wp-cli command from our command, it breaks and never continues (with xdebug).
        // So we need to turn xdebug off for all "nested" commands.
        if (isset($_SERVER["XDEBUG_CONFIG"])) {
            $env = $_SERVER;
            unset($env["XDEBUG_CONFIG"]);
        } else {
            $env = null;
        }

        $process = new Process($command, ABSPATH, $env);
        $process->run();
        return $process;
    }
}