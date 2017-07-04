<?php

namespace VersionPress\Tests\GitRepositoryTests;

use VersionPress\Actions\ActionsInfoProvider;
use VersionPress\Cli\VPCommandUtils;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Git\GitRepository;
use VersionPress\Tests\Utils\CommitAsserter;
use VersionPress\Utils\FileSystem;

class RevertTest extends \PHPUnit_Framework_TestCase
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
        VPCommandUtils::exec('git config user.name test', self::$repositoryPath);
        VPCommandUtils::exec('git config user.email test@example.com', self::$repositoryPath);

        $this->commitFile('initial-file', 'Initial commit');
    }

    /**
     * @test
     */
    public function revertTakesBackChangesFromLastCommit()
    {
        $this->commitFile('some-file', 'Some commit');
        $hash = self::$repository->getLastCommitHash();

        $commitAsserter = $this->createCommitAsserter();

        $revertResult = self::$repository->revert($hash);
        $this->commit('Revert');

        $this->assertTrue($revertResult);
        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCleanWorkingDirectory();
        $commitAsserter->assertCountOfAffectedFiles(1);
        $commitAsserter->assertCommitPath('D', 'some-file');
    }

    /**
     * @test
     */
    public function revertTakesBackChangesFromSpecificCommit()
    {
        $this->commitFile('some-file', 'Some commit');
        $hash = self::$repository->getLastCommitHash();
        $this->commitFile('other-file', 'Other commit');


        $commitAsserter = $this->createCommitAsserter();

        $revertResult = self::$repository->revert($hash);
        $this->commit('Revert');

        $this->assertTrue($revertResult);
        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCleanWorkingDirectory();
        $commitAsserter->assertCountOfAffectedFiles(1);
        $commitAsserter->assertCommitPath('D', 'some-file');
    }

    /**
     * @test
     */
    public function revertCannotTakeBackOverwrittenChanges()
    {
        $this->commitFile('some-file', 'Some commit', 'Some content');
        $hash = self::$repository->getLastCommitHash();
        $this->commitFile('some-file', 'Other commit', 'Other content');


        $commitAsserter = $this->createCommitAsserter();

        $revertResult = self::$repository->revert($hash);
        $this->commit('Revert');

        $this->assertFalse($revertResult);
        $commitAsserter->assertNumCommits(0);
        $commitAsserter->assertCleanWorkingDirectory();
    }

    /**
     * @test
     */
    public function revertAllTakesBackAllChanges()
    {
        $hash = self::$repository->getLastCommitHash();
        $this->commitFile('some-file', 'Some commit');
        $this->commitFile('other-file', 'Other commit');


        $commitAsserter = $this->createCommitAsserter();

        self::$repository->revertAll($hash);
        $this->commit('Revert all');

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCleanWorkingDirectory();
        $commitAsserter->assertCountOfAffectedFiles(2);
        $commitAsserter->assertCommitPath('D', 'some-file');
        $commitAsserter->assertCommitPath('D', 'other-file');
    }

    /**
     * @test
     */
    public function revertAllTakesBackChangesFromMultipleBranches()
    {
        $hash = self::$repository->getLastCommitHash();
        VPCommandUtils::exec('git branch test', self::$repositoryPath);
        $this->commitFile('some-file', 'Some commit');
        VPCommandUtils::exec('git checkout test', self::$repositoryPath);
        $this->commitFile('other-file', 'Other commit');
        VPCommandUtils::exec('git checkout master', self::$repositoryPath);
        VPCommandUtils::exec('git merge test', self::$repositoryPath);


        $commitAsserter = $this->createCommitAsserter();

        self::$repository->revertAll($hash);
        $this->commit('Revert all');

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCleanWorkingDirectory();
        $commitAsserter->assertCountOfAffectedFiles(2);
        $commitAsserter->assertCommitPath('D', 'some-file');
        $commitAsserter->assertCommitPath('D', 'other-file');
    }

    /**
     * @test
     */
    public function revertAllCanRevertToCommitInParallelBranch()
    {
        VPCommandUtils::exec('git branch test', self::$repositoryPath);
        $this->commitFile('some-file', 'Some commit');
        VPCommandUtils::exec('git checkout test', self::$repositoryPath);
        $this->commitFile('other-file', 'Other commit');
        $hash = self::$repository->getLastCommitHash();
        VPCommandUtils::exec('git checkout master', self::$repositoryPath);
        VPCommandUtils::exec('git merge test', self::$repositoryPath);


        $commitAsserter = $this->createCommitAsserter();

        self::$repository->revertAll($hash);
        $this->commit('Revert all');

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCleanWorkingDirectory();
        $commitAsserter->assertCountOfAffectedFiles(1);
        $commitAsserter->assertCommitPath('D', 'some-file');
    }

    private function commit($message)
    {
        self::$repository->commit($message, 'Author name', 'author@example.com');
    }

    private function commitFile($file, $commitMessage, $fileContent = '')
    {
        file_put_contents(self::$repositoryPath . '/' . $file, $fileContent);
        self::$repository->stageAll();
        $this->commit($commitMessage);
    }

    private function createCommitAsserter()
    {
        $dbSchemaInfo = $this->getMockBuilder(DbSchemaInfo::class)->disableOriginalConstructor()->getMock();
        $actionsInfoProvider = $this->getMockBuilder(ActionsInfoProvider::class)->disableOriginalConstructor()->getMock();
        return new CommitAsserter(self::$repository, $dbSchemaInfo, $actionsInfoProvider);
    }
 }
