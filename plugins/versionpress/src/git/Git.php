<?php

abstract class Git {

    private static $gitRoot = null;

    // Constants
    private static $ADD_AND_COMMIT_COMMAND = "git add -A %s && git commit -F %s";
    private static $RELATIVE_PATH_TO_GIT_ROOT_COMMAND = "git rev-parse --show-cdup";
    private static $INIT_COMMAND = "git init -q";
    private static $ASSUME_UNCHANGED_COMMAND = "git update-index --assume-unchanged %s";
    private static $COMMIT_MESSAGE_PREFIX = "[VP] ";
    private static $CONFIG_COMMAND = "git config user.name %s && git config user.email %s";
    private static $LOG_COMMAND = "git log --pretty=format:\"%%H|delimiter|%%aD|delimiter|%%ar|delimiter|%%an|delimiter|%%ae|delimiter|%%s|delimiter|%%b|end|\"";

    static function commit($message, $directory = "") {
        if(is_string($message))
            $message = new CommitMessage($message);

        chdir(dirname(__FILE__));
        if ($directory === "" && self::$gitRoot === null) {
            self::detectGitRoot();
        }
        $directory = $directory === "" ? self::$gitRoot : $directory;
        $gitAddPath = $directory . "/" . "*";

        if(is_user_logged_in() && is_admin()) {
            $currentUser = wp_get_current_user();
            $authorName = $currentUser->display_name;
            $authorEmail = $currentUser->user_email;
        } else {
            $authorName = "Non-admin action";
            $authorEmail = "nonadmin@example.com";
        }

        $commitMessage = self::$COMMIT_MESSAGE_PREFIX . $message->getHead();
        if($message->getBody() != null) $commitMessage .= "\n\n" . $message->getBody();
        $tempCommitMessageFilename = md5(rand());
        $tempCommitMessagePath = VERSIONPRESS_PLUGIN_DIR . "/temp/" . $tempCommitMessageFilename;
        file_put_contents($tempCommitMessagePath , $commitMessage);

        self::runShellCommand(self::$CONFIG_COMMAND, $authorName, $authorEmail);
        self::runShellCommand(self::$ADD_AND_COMMIT_COMMAND, $gitAddPath, $tempCommitMessagePath);
        unlink($tempCommitMessagePath);
    }

    static function isVersioned($directory) {
        chdir($directory);
        return self::runShellCommandWithStandardOutput('git status') !== '';
    }

    static function createGitRepository($directory) {
        chdir($directory);
        self::runShellCommand(self::$INIT_COMMAND);
    }

    private static function detectGitRoot() {
        self::$gitRoot = trim(self::runShellCommandWithStandardOutput(self::$RELATIVE_PATH_TO_GIT_ROOT_COMMAND), "/\n");
        self::$gitRoot = self::$gitRoot === '' ? '.' : self::$gitRoot;
    }

    private static function runShellCommandWithStandardOutput($command, $args = '') {
        $result = call_user_func_array(array('Git', 'runShellCommand'), func_get_args());
        return $result['stdout'];
    }

    private static function runShellCommandWithErrorOutput($command, $args = '') {
        $result = call_user_func_array(array('Git', 'runShellCommand'), func_get_args());
        return $result['stderr'];
    }

    private static function runShellCommand($command, $args = '') {
        $commandWithArguments = call_user_func_array('Git::prepareCommand', func_get_args());
        NDebugger::log('Running command: ' . $commandWithArguments);
        NDebugger::log('CWD: ' . getcwd());
        $result = self::runProcess($commandWithArguments);
        NDebugger::log('STDOUT: ' . $result['stdout']);
        NDebugger::log('STDERR: ' . $result['stderr']);
        return $result;
    }

    public static function pull() {
        self::runShellCommand("git pull -s recursive -X theirs origin master");
    }

    public static function push() {
        self::runShellCommand("git push origin master");
    }

    public static function assumeUnchanged($filename) {
        self::runShellCommand(self::$ASSUME_UNCHANGED_COMMAND, $filename);
    }

    /**
     * @return Commit[]
     */
    public static function log() {
        $commitDelimiter = chr(29);
        $dataDelimiter = chr(30);
        $logCommand = str_replace("|delimiter|", $dataDelimiter, self::$LOG_COMMAND);
        $logCommand = str_replace("|end|", $commitDelimiter, $logCommand);
        $log = trim(self::runShellCommandWithStandardOutput($logCommand), $commitDelimiter);
        $commits = explode($commitDelimiter, $log);
        return array_map(function ($rawCommit) {
            return Commit::buildFromString($rawCommit);
        }, $commits);
    }

    public static function revertAll($commit) {
        self::detectGitRoot();
        chdir(self::$gitRoot);
        $commitRange = sprintf("%s..HEAD", $commit);
        self::runShellCommand("git revert -n %s", $commitRange);
    }

    public static function revert($commit) {
        self::detectGitRoot();
        chdir(self::$gitRoot);
        $output = self::runShellCommandWithErrorOutput("git revert -n %s", $commit);

        if($output !== "") { // revert conflict
            self::runShellCommand("git revert --abort");
            return false;
        }

        return true;
    }

    private static function runProcess($cmd) {
        $descriptor = array(
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w')
        );

        $process = proc_open($cmd, $descriptor, $pipes, getcwd());

        $result = array(
            'stdout' => '',
            'stderr' => ''
        );

        if(is_resource($process)) {
            $result['stdout'] = stream_get_contents($pipes[1]);
            $result['stderr'] = stream_get_contents($pipes[2]);
        }

        proc_close($process);

        return $result;
    }

    private static function prepareCommand($command, $args = '') {
        $functionArgs = func_get_args();
        array_shift($functionArgs); // Remove $command
        $escapedArgs = @array_map("escapeshellarg", $functionArgs);
        $commandWithArguments = vsprintf($command, $escapedArgs);
        return $commandWithArguments;
    }
}