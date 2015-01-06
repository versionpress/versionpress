<?php

namespace VersionPress\Git;

use NStrings;
use Symfony\Component\Process\Process;
use VersionPress\Utils\FileSystem;

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
    private static $UPDATE_COMMAND = "git add -A %s"; // adds new files and removes deleted files
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
    private static $COUNT_COMMITS_COMMAND = "git rev-list %s --count";


    function __construct($repositoryRoot, $tempDirectory = "./", $commitMessagePrefix = "[VP] ") {
        $this->repositoryRoot = $repositoryRoot;
        $this->tempDirectory = $tempDirectory;
        $this->commitMessagePrefix = $commitMessagePrefix;
    }

    public function add($path) {
        $this->runShellCommand(self::$ADD_COMMAND, $path);
    }

    public function update($path) {
        $this->runShellCommand(self::$UPDATE_COMMAND, $path);
    }

    /**
     * @param CommitMessage $message
     * @param string $authorName
     * @param string $authorEmail
     */
    public function commit($message, $authorName = "", $authorEmail = "") {
        $this->authorName = $authorName;
        $this->authorEmail = $authorEmail;

        $subject = $message->getSubject();
        $body = $message->getBody();
        $commitMessage = $this->commitMessagePrefix . $subject;

        if ($body != null) $commitMessage .= "\n\n" . $body;

        $tempCommitMessageFilename = md5(rand());
        $tempCommitMessagePath = $this->tempDirectory . $tempCommitMessageFilename;
        file_put_contents($tempCommitMessagePath, $commitMessage);

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

    /**
     * Gets last (most recent) commit hash in the repository, or an empty string is there are no commits
     * in the repo.
     *
     * @return string Empty string or SHA1
     */
    public function getLastCommitHash() {
        $result = $this->runShellCommand(self::$REV_PARSE_COMMAND, "HEAD");
        if ($result["stderr"]) {
            return "";
        } else {
            return $result["stdout"];
        }
    }

    /**
     * @return Commit
     */
    public function getInitialCommit() {
        $initialCommitHash = $this->runShellCommandWithStandardOutput(self::$INITAL_COMMIT_HASH_COMMAND);
        return $this->getCommit($initialCommitHash);
    }

    /**
     * Returns an array of Commits.
     *
     * @param string $rev see gitrevisions
     * @return Commit[]
     */
    public function log($rev = "") {

        $commitDelimiter = chr(29);
        $dataDelimiter = chr(30);

        $logCommand = self::$LOG_COMMAND;
        if (!empty($rev)) {
            $logCommand .= " " . escapeshellarg($rev);
        }

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
        $result = $this->runShellCommandWithStandardOutput(self::$MODIFIED_FILES_COMMAND, $rev);
        $files = explode("\n", $result);
        return $files;
    }

    /**
     * Like getModifiedFiles() but also returns the status of each file ("A" for added,
     * "M" for modified, "D" for deleted and "R" for renamed).
     *
     * @param string $rev See gitrevisions
     * @return array( array("status" => "M", "path" => "wp-content/vpdb/something.ini" )
     */
    public function getModifiedFilesWithStatus($rev) {
        $command = 'git diff --name-status %s';
        $output = $this->runShellCommandWithStandardOutput($command, $rev);
        $result = array();

        foreach (explode("\n", $output) as $line) {
            list($status, $path) = explode("\t", $line);
            $result[] = array("status" => $status, "path" => $path);
        }

        return $result;

    }

    public function revertAll($commit) {
        $commitRange = sprintf("%s..HEAD", $commit);
        $this->runShellCommand(self::$REVERT_COMMAND, $commitRange);
    }

    public function revert($commit) {
        $output = $this->runShellCommandWithErrorOutput(self::$REVERT_COMMAND, $commit);

        if ($output !== null) { // revert conflict
            $this->abortRevert();
            return false;
        }

        return true;
    }

    public function abortRevert() {
        $this->runShellCommand(self::$REVERT_ABORT_COMMAND);
    }

    /**
     * Returns true if $commitHash was created after the $afterWhat commit ("after" meaning
     * that $commitHash is more recent commit, a child of $afterWhat). Same two commits return false.
     *
     * @param $commitHash
     * @param $afterWhat
     * @return bool
     */
    public function wasCreatedAfter($commitHash, $afterWhat) {
        $cmd = "git log $afterWhat..$commitHash --oneline";
        return $this->runShellCommandWithStandardOutput($cmd) != null;
    }

    /**
     * Returns child ("next") commit. Assumes there is only a single child commit.
     *
     * @param $commitHash
     * @return mixed
     */
    public function getChildCommit($commitHash) {
        $cmd = "git log --reverse --ancestry-path --format=%%H $commitHash^..";
        $result = $this->runShellCommandWithStandardOutput($cmd);
        list($childHash) = explode("\n", $result);
        return $childHash;
    }

    /**
     * Counts number of commits.
     *
     * @param string $startRevision Where to start. NULL means the initial commit (repo start)
     * @param string $endRevision Where to end. This will typically be HEAD.
     * @return int
     */
    public function getNumberOfCommits($startRevision = null, $endRevision = "HEAD") {

        $revRange = empty($startRevision) ? $endRevision : "$startRevision..$endRevision";

        return intval($this->runShellCommandWithStandardOutput(self::$COUNT_COMMITS_COMMAND, $revRange));
    }

    /**
     * Invokes {@see runShellCommand()} and returns its stdout output. The params are the same,
     * only the return type is string instead of an array.
     *
     * @see runShellCommand()
     * @see runShellCommandWithErrorOutput()
     *
     * @param string $command
     * @param string $args
     * @return string
     */
    private function runShellCommandWithStandardOutput($command, $args = '') {
        $result = call_user_func_array(array($this, 'runShellCommand'), func_get_args());
        return $result['stdout'];
    }

    /**
     * Invokes {@see runShellCommand()} and returns its stderr output. The params are the same,
     * only the return type is string instead of an array.
     *
     * @see runShellCommand()
     * @see runShellCommandWithStandardOutput()
     *
     * @param string $command
     * @param string $args
     * @return string
     */
    private function runShellCommandWithErrorOutput($command, $args = '') {
        $result = call_user_func_array(array($this, 'runShellCommand'), func_get_args());
        return $result['stderr'];
    }

    /**
     * Run a Git command, either fully specified (e.g., 'git log') or just by the name (e.g., 'log').
     * The comamnd can contain `sprintf()` markers, e.g., '%s', which are then replaced by shell-escaped
     * args provided as further parameters to this method.
     *
     * Note: shell-escaping is actually pretty important even for things that are not paths, like revisions.
     * For example, `git log HEAD^` will not work on Windows, `git log "HEAD^"` will. So the right
     * approach is to provide `git log %s` as the $command and rev range as $args.
     *
     * @param string $command E.g., 'git log' or 'git add %s' (path will be shell-escaped) or just 'log'
     *   (the "git " part is optional).
     * @param string $args Will be shell-escaped and replace sprintf markers in $command
     * @return array array('stdout' => , 'stderr' => )
     */
    public function runShellCommand($command, $args = '') {

        if (!NStrings::startsWith($command, "git ")) {
            $command = "git " . $command;
        }

        $functionArgs = func_get_args();
        array_shift($functionArgs); // Remove $command
        $escapedArgs = @array_map("escapeshellarg", $functionArgs);
        $commandWithArguments = vsprintf($command, $escapedArgs);

        chdir($this->repositoryRoot); // TODO maybe just pass repository root to Symfony\Process in runProcess()

        $result = $this->runProcess($commandWithArguments);
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
        if ($dyldLibraryPath != "") {
            putenv("DYLD_LIBRARY_PATH=");

        }

        $process = new Process($cmd, getcwd());
        $process->run();

        $result = array(
            'stdout' => $process->getOutput(),
            'stderr' => $process->getErrorOutput()
        );

        putenv("DYLD_LIBRARY_PATH=$dyldLibraryPath");

        if ($result['stdout'] !== null) $result['stdout'] = trim($result['stdout']);
        if ($result['stderr'] !== null) $result['stderr'] = trim($result['stderr']);

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

    /**
     * Returns git status in short format, something like:
     *
     *     A path1.txt
     *     M path2.txt
     *
     * Clean working directory returns an empty string.
     *
     * @return string
     */
    public function getStatus() {
        $gitCmd = "git status --porcelain";
        $output = $this->runShellCommandWithStandardOutput($gitCmd);
        return $output;
    }
}
