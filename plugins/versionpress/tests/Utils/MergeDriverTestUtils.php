<?php

namespace VersionPress\Tests\Utils;

use VersionPress\Git\GitConfig;
use VersionPress\Git\GitRepository;
use VersionPress\Git\MergeDriverInstaller;
use VersionPress\Utils\FileSystem;
use VersionPress\Utils\IniSerializer;
use VersionPress\Utils\Process;

class MergeDriverTestUtils {

    private static $repositoryDir;

    /**
     * @var GitRepository
     */
    private static $gitRepository;

    public static function initRepository($repositoryDir) {
        self::$repositoryDir = $repositoryDir;
        $driverScriptName = 'ini-merge.php';
        $driverScript = '../../src/Git/merge-drivers/' . $driverScriptName;
        $driverScriptFakeDir = self::$repositoryDir . '/src/Git/merge-drivers';
        FileSystem::remove(self::$repositoryDir);
        mkdir(self::$repositoryDir);
        FileSystem::mkdir($driverScriptFakeDir);
        self::$gitRepository = new GitRepository(self::$repositoryDir, __DIR__);
        self::$gitRepository->init();
        copy($driverScript, $driverScriptFakeDir . '/' . $driverScriptName);
        $driverScriptName = 'ini-merge.sh';
        $driverScript = '../../src/Git/merge-drivers/' . $driverScriptName;
        copy($driverScript, $driverScriptFakeDir . '/' . $driverScriptName);

    }


    public static function destroyRepository() {
        FileSystem::remove(self::$repositoryDir);
    }


    public static function writeIniFile($fileName, $date, $content = 'Default content', $title = 'Default title') {
        $data = array("GUID" => array('post_modified' => $date, 'post_modified_gmt' => $date, 'title' => $title, 'content' => $content));
        file_put_contents(self::$repositoryDir . '/' . $fileName, IniSerializer::serialize($data));
    }

    public static function createIniFileWithoutDateFields($fileName, $content = 'Default content', $title = 'Default title') {
        $data = array("GUID" => array('title' => $title, 'content' => $content));
        file_put_contents(self::$repositoryDir . '/' . $fileName, IniSerializer::serialize($data));
    }

    public static function commit($message = 'Default commit message') {
        self::$gitRepository->stageAll();
        self::$gitRepository->commit($message, GitConfig::$wpcliUserName, GitConfig::$wpcliUserEmail);
    }

    public static function createIniFileAndCommit($originDate, $fileName, $message, $content = 'Default content', $title = 'Default title') {
        self::writeIniFile($fileName, $originDate, $content, $title);
        self::commit($message);
    }

    public static function createIniFileWithoutDateFieldsAndCommit($fileName, $message, $content = 'Default content', $title = 'Default title') {
        self::createIniFileWithoutDateFields($fileName, $content, $title);
        self::commit($message);
    }

    /**
     * Runs Git command in the test repo and returns the exit code.
     * 
     * @param string $cmd
     * @return int Exit code
     */
    public static function runGitCommand($cmd) {
        $process = new Process($cmd, self::$repositoryDir);
        $process->run();
        return $process->getExitCode();
    }


}
