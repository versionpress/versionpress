<?php

namespace VersionPress\Tests\Utils;

use VersionPress\Git\GitConfig;
use VersionPress\Git\GitRepository;
use VersionPress\Storages\Serialization\IniSerializer;
use VersionPress\Utils\FileSystem;
use VersionPress\Utils\Process;

class MergeDriverTestUtils
{

    private static $repositoryDir;

    /**
     * @var GitRepository
     */
    private static $gitRepository;

    public static function initRepository($repositoryDir)
    {
        self::$repositoryDir = $repositoryDir;

        FileSystem::remove(self::$repositoryDir);
        mkdir(self::$repositoryDir);

        self::$gitRepository = new GitRepository(self::$repositoryDir, sys_get_temp_dir());
        self::$gitRepository->init();
        self::runGitCommand('git config user.name test');
        self::runGitCommand('git config user.email test@example.com');

        $driverScriptsDir = __DIR__ . '/../../src/Git/merge-drivers';
        $driverScriptsFakeDir = self::$repositoryDir . '/src/Git/merge-drivers';
        FileSystem::copyDir($driverScriptsDir, $driverScriptsFakeDir);
    }


    public static function destroyRepository()
    {
        FileSystem::remove(self::$repositoryDir);
    }


    public static function writeIniFile($fileName, $date, $content = 'Default content', $title = 'Default title')
    {
        $data = [
            "GUID" => [
                'post_modified' => $date,
                'post_modified_gmt' => $date,
                'title' => $title,
                'content' => $content
            ]
        ];
        file_put_contents(self::$repositoryDir . '/' . $fileName, IniSerializer::serialize($data));
    }

    public static function createIniFileWithoutDateFields(
        $fileName,
        $content = 'Default content',
        $title = 'Default title'
    ) {
        $data = ["GUID" => ['title' => $title, 'content' => $content]];
        file_put_contents(self::$repositoryDir . '/' . $fileName, IniSerializer::serialize($data));
    }

    public static function commit($message = 'Default commit message')
    {
        self::$gitRepository->stageAll();
        self::$gitRepository->commit($message, GitConfig::$wpcliUserName, GitConfig::$wpcliUserEmail);
    }

    public static function createIniFileAndCommit(
        $originDate,
        $fileName,
        $message,
        $content = 'Default content',
        $title = 'Default title'
    ) {
        self::writeIniFile($fileName, $originDate, $content, $title);
        self::commit($message);
    }

    public static function createIniFileWithoutDateFieldsAndCommit(
        $fileName,
        $message,
        $content = 'Default content',
        $title = 'Default title'
    ) {
        self::createIniFileWithoutDateFields($fileName, $content, $title);
        self::commit($message);
    }

    /**
     * Runs Git command in the test repo and returns the exit code.
     *
     * @param string $cmd
     * @return int Exit code
     */
    public static function runGitCommand($cmd)
    {
        $process = new Process($cmd, self::$repositoryDir);
        $process->run();
        return $process->getExitCode();
    }
}
