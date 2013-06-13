<?php

abstract class Git {

    private static $gitRoot = null;

    // Constants
    private static $ADD_AND_COMMIT_COMMAND = "git add -A %s && git commit -m %s";
    private static $RELPATH_TO_GIT_ROOT_COMMAND = "git rev-parse --show-cdup";

    static function commit($message, $directory = "") {
        chdir(dirname(__FILE__));
        if ($directory === "" && self::$gitRoot === null) {
            self::detectGitRoot();
        }
        $directory = $directory === "" ? self::$gitRoot : $directory;
        $gitAddPath = $directory . "/" . "*";

        $result = self::runShellCommand(self::$ADD_AND_COMMIT_COMMAND, $gitAddPath, $message);
    }

    private static function detectGitRoot() {
        self::$gitRoot = trim(self::runShellCommand(self::$RELPATH_TO_GIT_ROOT_COMMAND), "/\n");
        self::$gitRoot = self::$gitRoot === '' ? '.' : self::$gitRoot;
    }

    private static function runShellCommand($command, $args = '') {
        $functionArgs = func_get_args();
        array_shift($functionArgs); // Remove $command
        $escapedArgs = @array_map("escapeshellarg", $functionArgs);
        $commandWithArguments = vsprintf($command, $escapedArgs);
        return @shell_exec($commandWithArguments);
    }
}