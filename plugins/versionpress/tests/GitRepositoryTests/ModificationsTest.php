<?php

namespace VersionPress\Tests\GitRepositoryTests;

use VersionPress\Git\CommitMessage;
use VersionPress\Git\GitRepository;
use VersionPress\Utils\FileSystem;

class ModificationsTest extends \PHPUnit_Framework_TestCase
{

    private static $repositoryPath;
    private static $tempPath;
    /** @var GitRepository */
    private static $repository;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repositoryPath = sys_get_temp_dir() . '/vp-repository';
        self::$tempPath = sys_get_temp_dir() . '/vp-temp';
        FileSystem::remove(self::$repositoryPath);
        FileSystem::remove(self::$tempPath);

        self::$repository = new GitRepository(self::$repositoryPath, self::$tempPath);
        mkdir(self::$repositoryPath);
        mkdir(self::$tempPath);
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        FileSystem::remove(self::$repositoryPath);
        FileSystem::remove(self::$tempPath);
    }

    protected function setUp()
    {
        parent::setUp();
        FileSystem::removeContent(self::$repositoryPath);
        FileSystem::removeContent(self::$tempPath);
        self::$repository->init();
    }

    /**
     * @test
     */
    public function thereAreNoModificationsInEmptyRepository()
    {
        $modifications = self::$repository->getFileModifications('somefile');
        $this->assertEquals([], $modifications);
    }

    /**
     * @test
     */
    public function thereIsOneModificationAfterAdding()
    {
        touch(self::$repositoryPath . '/somefile');
        touch(self::$repositoryPath . '/otherfile');
        $this->commitEverything();

        $modifications = self::$repository->getFileModifications('somefile');
        $lastCommit = self::$repository->log()[0];

        $expectedModifications = [
            ['status' => 'A', 'path' => 'somefile', 'commit' => $lastCommit->getHash()],
        ];

        $this->assertEquals($expectedModifications, $modifications);
    }

    /**
     * @test
     */
    public function thereAreTwoModificationsAfterAddingAndDeleting()
    {
        touch(self::$repositoryPath . '/somefile');
        touch(self::$repositoryPath . '/otherfile');
        $this->commitEverything();
        unlink(self::$repositoryPath . '/somefile');
        $this->commitEverything();

        $modifications = self::$repository->getFileModifications('somefile');
        $log = self::$repository->log();

        $expectedModifications = [
            ['status' => 'D', 'path' => 'somefile', 'commit' => $log[0]->getHash()],
            ['status' => 'A', 'path' => 'somefile', 'commit' => $log[1]->getHash()],
        ];

        $this->assertEquals($expectedModifications, $modifications);
    }

    /**
     * @test
     */
    public function itSupportsWildcards()
    {
        touch(self::$repositoryPath . '/somefile');
        touch(self::$repositoryPath . '/otherfile');
        $this->commitEverything();
        unlink(self::$repositoryPath . '/somefile');
        $this->commitEverything();

        $modifications = self::$repository->getFileModifications('*file');
        $log = self::$repository->log();

        $expectedModifications = [
            ['status' => 'D', 'path' => 'somefile', 'commit' => $log[0]->getHash()],
            ['status' => 'A', 'path' => 'otherfile', 'commit' => $log[1]->getHash()],
            ['status' => 'A', 'path' => 'somefile', 'commit' => $log[1]->getHash()],
        ];

        $this->assertEquals($expectedModifications, $modifications);
    }

    private function commitEverything()
    {
        self::$repository->stageAll();
        self::$repository->commit(new CommitMessage("Some commit"), "Author name", "author@example.com");
    }
}
