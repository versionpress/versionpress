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
        $driverScript = '../../src/Git/MergeDrivers/' . $driverScriptName;
        $driverScriptFakeDir = self::$repositoryDir . '/src/Git/MergeDrivers';
        FileSystem::remove(self::$repositoryDir);
        mkdir(self::$repositoryDir);
        FileSystem::mkdir($driverScriptFakeDir);
        self::$gitRepository = new GitRepository(self::$repositoryDir, __DIR__);
        self::$gitRepository->init();
        copy($driverScript, $driverScriptFakeDir . '/' . $driverScriptName);

    }


    public static function destroyRepository() {
        FileSystem::remove(self::$repositoryDir);
    }

    public static function fillFakeFileAndCommit($originDate, $message = 'Fake commit message', $content = 'Fake content') {
        $originData = array("GUID" => array('post_modified' => $originDate, 'post_modified_gmt' => $originDate, 'content' => $content));
        file_put_contents(self::$repositoryDir . '/file.ini', IniSerializer::serialize($originData));
        self::$gitRepository->stageAll();
        self::$gitRepository->commit($message, GitConfig::$wpcliUserName, GitConfig::$wpcliUserEmail);
    }

    /**
     * @param $checkoutBranchCmd
     * @return Process
     */
    public static function runProcess($checkoutBranchCmd) {
        $process = new Process($checkoutBranchCmd, self::$repositoryDir);
        $process->run();
        return $process->getExitCode();
    }

    public static function installMergeDriver($initializationDir) {
        MergeDriverInstaller::installGitattributes($initializationDir);
        MergeDriverInstaller::installGitMergeDriver($initializationDir);
    }


}
