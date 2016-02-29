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


    public static function createIniFile($date, $fileName, $content = 'Default content', $title = 'Default title') {
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
        self::createIniFile($originDate, $fileName, $content, $title);
        self::commit($message);
    }

    public static function createIniFileWithoutDateFieldsAndCommit($fileName, $message, $content = 'Default content', $title = 'Default title') {
        self::createIniFileWithoutDateFields($fileName, $content, $title);
        self::commit($message);
    }

    /**
     * @param $checkoutBranchCmd
     * @return int
     */
    public static function getProcessExitCode($checkoutBranchCmd) {
        $process = new Process($checkoutBranchCmd, self::$repositoryDir);
        $process->run();
        return $process->getExitCode();
    }

    public static function switchDriverToBash() {
        $driverScriptName = 'ini-merge.sh';
        $driverScript = '../../src/Git/merge-drivers/' . $driverScriptName;
        $driverScriptFakeDir = self::$repositoryDir . '/src/Git/merge-drivers';
        copy($driverScript, $driverScriptFakeDir . '/' . $driverScriptName);
        chmod($driverScriptFakeDir . '/' . $driverScriptName, 0774);

        $gitconfig = file_get_contents(self::$repositoryDir . '/.git/config');
        $gitconfig = preg_replace('/\n?.*driver = .*$/m', "\n" . 'driver = ' . $driverScriptFakeDir . '/' . $driverScriptName . ' %O %A %B' . "\n", $gitconfig);
        file_put_contents(self::$repositoryDir . '/.git/config', $gitconfig);

    }

    public static function switchDriverToPhp() {
        $driverScriptName = 'ini-merge.php';
        $driverScript = '../../src/Git/merge-drivers/' . $driverScriptName;
        $driverScriptFakeDir = self::$repositoryDir . '/src/Git/merge-drivers';
        copy($driverScript, $driverScriptFakeDir . '/' . $driverScriptName);
        chmod($driverScriptFakeDir . '/' . $driverScriptName, 0774);

        $mergeDriverScript = '"' . PHP_BINARY . '" "' . $driverScriptFakeDir . '/' . $driverScriptName;

        $gitconfig = file_get_contents(self::$repositoryDir . '/.git/config');
        $gitconfig = preg_replace('/\n?.*driver = .*$/m', "\n" . 'driver = ' . str_replace('\\', '/', $mergeDriverScript) . '" %O %A %B' . "\n", $gitconfig);
        file_put_contents(self::$repositoryDir . '/.git/config', $gitconfig);

    }


}
