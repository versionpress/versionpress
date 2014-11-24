<?php

class GitRepository {
    /** @var string */
    private $repositoryRoot;
    /** @var string */
    private $authorName = "";
    /** @var string */
    private $authorEmail = "";
    /** @var string */
    private $tempDirectory;
    /** @var string */
    private $commitMessagePrefix;
    // Constants

    private static $ADD_COMMAND = "git add %s";
    private static $UPDATE_COMMAND = "git add -u %s"; // removes deleted files
    private static $COMMIT_COMMAND = "git commit -F %s";
    private static $INIT_COMMAND = "git init";
    private static $STATUS_COMMAND = "git status -s";
    private static $INITAL_COMMIT_HASH_COMMAND = "git rev-list --max-parents=0 HEAD";
    private static $ASSUME_UNCHANGED_COMMAND = "git update-index --assume-unchanged %s";
    private static $MODIFIED_FILES_COMMAND = "git diff --name-only %s";
    private static $CONFIG_COMMAND = "git config user.name %s && git config user.email %s";
    private static $LOG_COMMAND = "git log --pretty=format:\"%%H|delimiter|%%aD|delimiter|%%ar|delimiter|%%an|delimiter|%%ae|delimiter|%%s|delimiter|%%b|end|\"";
    private static $REV_PARSE_COMMAND = "git rev-parse %s";
    private static $REVERT_COMMAND = "git revert -n %s";
    private static $REVERT_ABORT_COMMAND = "git revert --abort";
    private static $COUNT_COMMITS_COMMAND = "git rev-list HEAD --count";


    function __construct($repositoryRoot, $tempDirectory = "./", $commitMessagePrefix = "[VP] ") {
        $this->repositoryRoot = $repositoryRoot;
        $this->tempDirectory = $tempDirectory;
        $this->commitMessagePrefix = $commitMessagePrefix;
    }

    public function add($path) {
        $this->runShellCommand(self::$ADD_COMMAND, $path);
    }

    public function rm($path) {
        $this->runShellCommand(self::$UPDATE_COMMAND, $path);
    }

    /**
     * @param CommitMessage $message
     * @param string $authorName
     * @param string $authorEmail
     */
    public function commit($message, $authorName = "", $authorEmail= "") {
        $this->authorName = $authorName;
        $this->authorEmail = $authorEmail;

        $subject = $message->getSubject();
        $body = $message->getBody();
        $commitMessage = $this->commitMessagePrefix . $subject;

        if ($body != null) $commitMessage .= "\n\n" . $body;

        $tempCommitMessageFilename = md5(rand());
        $tempCommitMessagePath = $this->tempDirectory . $tempCommitMessageFilename;
        file_put_contents($tempCommitMessagePath , $commitMessage);

        $this->runShellCommand(self::$CONFIG_COMMAND, $this->authorName, $this->authorEmail);
        $this->runShellCommand(self::$COMMIT_COMMAND, $tempCommitMessagePath);
        FileSystem::remove($tempCommitMessagePath);
    }

    public function isVersioned() {
        return $this->runShellCommandWithStandardOutput(self::$STATUS_COMMAND) !== null;
    }

    public function init() {
        $this->runShellCommand(self::$INIT_COMMAND);
    }

    public function pull() {
        $this->runShellCommand("git pull -s recursive -X theirs origin master");
    }

    public function push() {
        $this->runShellCommand("git push origin master");
    }

    public function assumeUnchanged($filename) {
        $this->runShellCommand(self::$ASSUME_UNCHANGED_COMMAND, $filename);
    }

    public function getLastCommitHash() {
        return $this->runShellCommandWithStandardOutput(self::$REV_PARSE_COMMAND, "HEAD");
    }

    /**
     * @return Commit
     */
    public function getInitialCommit() {
        $initialCommitHash = $this->runShellCommandWithStandardOutput(self::$INITAL_COMMIT_HASH_COMMAND);
        return $this->getCommit($initialCommitHash);
    }

    /**
     * @param string $rev see gitrevisions
     * @return Commit[]
     */
    public function log($rev = "") {
        $commitDelimiter = chr(29);
        $dataDelimiter = chr(30);
        $logCommand = self::$LOG_COMMAND . " " . $rev;
        $logCommand = str_replace("|delimiter|", $dataDelimiter, $logCommand);
        $logCommand = str_replace("|end|", $commitDelimiter, $logCommand);
        $log = trim($this->runShellCommandWithStandardOutput($logCommand), $commitDelimiter);
        $commits = explode($commitDelimiter, $log);
        return array_map(function ($rawCommit) {
            return Commit::buildFromString(trim($rawCommit));
        }, $commits);
    }

    /**
     * Returns list of files that were modified in given revision.
     * @param string $rev see gitrevisions
     * @return string[]
     */
    public function getModifiedFiles($rev) {
        $cmd = sprintf(self::$MODIFIED_FILES_COMMAND, $rev);
        $result = $this->runShellCommandWithStandardOutput($cmd);
        $files = explode("\n", $result);
        return $files;
    }

    public function revertAll($commit) {
        $commitRange = sprintf("%s..HEAD", $commit);
        $this->runShellCommand(self::$REVERT_COMMAND, $commitRange);
    }

    public function revert($commit) {
        $output = $this->runShellCommandWithErrorOutput(self::$REVERT_COMMAND, $commit);

        if($output !== null) { // revert conflict
            $this->runShellCommand(self::$REVERT_ABORT_COMMAND);
            return false;
        }

        return true;
    }

    public function wasCreatedAfter($commitHash, $afterWhat) {
        $cmd = "git log $afterWhat..$commitHash --oneline";
        return $this->runShellCommandWithStandardOutput($cmd) != null;
    }

    public function getNumberOfCommits() {
        return intval($this->runShellCommandWithStandardOutput(self::$COUNT_COMMITS_COMMAND));
    }

    private function runShellCommandWithStandardOutput($command, $args = '') {
        $result = call_user_func_array(array($this, 'runShellCommand'), func_get_args());
        return $result['stdout'];
    }

    private function runShellCommandWithErrorOutput($command, $args = '') {
        $result = call_user_func_array(array($this, 'runShellCommand'), func_get_args());
        return $result['stderr'];
    }

    private function runShellCommand($command, $args = '') {
        $functionArgs = func_get_args();
        array_shift($functionArgs); // Remove $command
        $escapedArgs = @array_map("escapeshellarg", $functionArgs);
        $commandWithArguments = vsprintf($command, $escapedArgs);

        chdir($this->repositoryRoot);

        NDebugger::log('Running command: ' . $commandWithArguments);
        NDebugger::log('CWD: ' . getcwd());
        $result = $this->runProcess($commandWithArguments);
        NDebugger::log('STDOUT: ' . $result['stdout']);
        NDebugger::log('STDERR: ' . $result['stderr']);
        return $result;
    }

    private function runProcess($cmd) {
        /*
         * MAMP / XAMPP issue on Mac OS X,
         * see http://jira.agilio.cz/browse/WP-106.
         *
         * http://stackoverflow.com/a/16903162/1243495
         */
        $dyldLibraryPath = getenv("DYLD_LIBRARY_PATH");
        if($dyldLibraryPath != "") {
            putenv("DYLD_LIBRARY_PATH=");

        }

        $process = new \Symfony\Component\Process\Process($cmd, getcwd());
        $process->run();

        $result = array(
            'stdout' => $process->getOutput(),
            'stderr' => $process->getErrorOutput()
        );

        putenv("DYLD_LIBRARY_PATH=$dyldLibraryPath");

        if($result['stdout'] !== null) $result['stdout'] = trim($result['stdout']);
        if($result['stderr'] !== null) $result['stderr'] = trim($result['stderr']);

        return $result;
    }

    public function willCommit() {
        $status = $this->runShellCommandWithStandardOutput(self::$STATUS_COMMAND);
        return NStrings::match($status, "~^[AMD].*~") !== null;
    }

    /**
     * @param $commitHash
     * @return Commit
     */
    public function getCommit($commitHash) {
        $logWithInitialCommit = $this->log($commitHash);
        return $logWithInitialCommit[0];
    }
}