<?php

namespace VersionPress\Tests\LoadTests;

use VersionPress\Git\GitRepository;
use VersionPress\Utils\FileSystem;
use VersionPress\Utils\Process;

class CommitterTest extends \PHPUnit_Framework_TestCase
{

    private static $repositoryDir;

    public static function setUpBeforeClass()
    {
        self::$repositoryDir = __DIR__ . '/repository';
        FileSystem::remove(self::$repositoryDir);
        mkdir(self::$repositoryDir);
    }

    public static function tearDownAfterClass()
    {
        FileSystem::remove(self::$repositoryDir);
    }

    /**
     * @test
     */
    public function committerIsThreadSafe()
    {
        $gitRepository = new GitRepository(self::$repositoryDir, __DIR__);
        $gitRepository->init();

        $numberOfParallelCommits = 50;
        $numberOfFilesInEachCommit = 10;

        /** @var Process[] $runningProcesses */
        $runningProcesses = [];
        for ($i = 0; $i < $numberOfParallelCommits; $i++) {
            $from = $i * $numberOfFilesInEachCommit;
            $to = ($i + 1) * $numberOfFilesInEachCommit;

            $process = new Process("php generate-files-and-commit.php --from=$from --to=$to", __DIR__);
            $process->start();
            $runningProcesses[] = $process;
        }

        foreach ($runningProcesses as $process) {
            $process->wait();
        }

        $log = $gitRepository->log();
        $this->assertCount($numberOfParallelCommits, $log);

        foreach ($log as $commit) {
            $files = $commit->getChangedFiles();
            $this->assertCount(
                $numberOfFilesInEachCommit,
                $files,
                "Files: \n" . join("\n", array_column($files, 'path'))
            );
        }
    }
}
