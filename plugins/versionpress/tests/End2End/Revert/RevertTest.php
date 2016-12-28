<?php

namespace VersionPress\Tests\End2End\Revert;

use VersionPress\Cli\VPCommandUtils;
use VersionPress\Tests\End2End\Utils\End2EndTestCase;
use VersionPress\Tests\Utils\CommitAsserter;
use VersionPress\Tests\Utils\DBAsserter;

class RevertTest extends End2EndTestCase
{

    /** @var IRevertTestWorker */
    private static $worker;

    /**
     * @test
     * @testdox Undo reverts changes in given commit
     */
    public function undoRevertChangesInGivenCommit()
    {
        $changes = self::$worker->prepare_undoLastCommit();

        $this->commitAsserter->reset();

        self::$worker->undoLastCommit();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction('versionpress/undo');
        $this->commitAsserter->assertCountOfAffectedFiles(count($changes));
        $this->commitAsserter->assertCommitPaths($changes);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Undo reverts only one commit
     */
    public function undoRevertsOnlyOneCommit()
    {
        $changes = self::$worker->prepare_undoSecondCommit();
        $this->commitAsserter->reset();

        self::$worker->undoSecondCommit();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction('versionpress/undo');
        $this->commitAsserter->assertCountOfAffectedFiles(count($changes));
        $this->commitAsserter->assertCommitPaths($changes);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Undo commit can be also reverted.
     */
    public function undoCommitCanBeAlsoReverted()
    {
        $changes = self::$worker->prepare_undoRevertedCommit();

        $this->commitAsserter->reset();

        self::$worker->undoLastCommit();
        self::$worker->undoLastCommit();

        $this->commitAsserter->assertNumCommits(2);
        $this->commitAsserter->assertCommitAction('versionpress/undo');
        $this->commitAsserter->assertCountOfAffectedFiles(count($changes));
        $this->commitAsserter->assertCommitPaths($changes);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Comment deletion cannot be reverted if the commented post no longer exists
     */
    public function entityWithMissingReferenceCannotBeRestoredWithRevert()
    {
        self::$worker->prepare_tryRestoreEntityWithMissingReference();

        $this->commitAsserter->reset();

        self::$worker->tryRestoreEntityWithMissingReference();
        $this->commitAsserter->assertNumCommits(0);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Rollback reverts all changes made after chosen commit
     */
    public function rollbackRevertsAllChangesMadeAfterChosenCommit()
    {
        $changes = self::$worker->prepare_rollbackMoreChanges();

        $this->commitAsserter->reset();

        self::$worker->rollbackMoreChanges();
        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction('versionpress/rollback');
        $this->commitAsserter->assertCountOfAffectedFiles(count($changes));
        $this->commitAsserter->assertCommitPaths($changes);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Clicking on Cancel only hides the popup
     */
    public function clickingOnCancelOnlyHidesThePopup()
    {
        self::$worker->prepare_clickOnCancel();

        $this->commitAsserter->reset();

        self::$worker->clickOnCancel();

        $this->commitAsserter->assertNumCommits(0);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox OK button is disabled if the working directory is not clean
     */
    public function undoDoesNothingIfTheWorkingDirectoryIsNotClean()
    {
        self::$worker->prepare_undoWithNotCleanWorkingDirectory();

        $this->commitAsserter->reset();
        touch(self::$testConfig->testSite->path . '/revert-test-file');

        self::$worker->undoLastCommit();

        $this->commitAsserter->assertNumCommits(0);
        unlink(self::$testConfig->testSite->path . '/revert-test-file');
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     */
    public function rollbackWorksWithMergeCommits()
    {
        $commitHash = $this->gitRepository->getLastCommitHash();
        $sitePath = self::$testConfig->testSite->path;

        VPCommandUtils::exec('git branch test', $sitePath);
        self::$wpAutomation->createOption('vp_option_master', 'foo');
        VPCommandUtils::exec('git checkout test', $sitePath);
        self::$wpAutomation->createOption('vp_option_test', 'foo');
        VPCommandUtils::exec('git checkout master', $sitePath);
        VPCommandUtils::exec('git merge test', $sitePath);
        VPCommandUtils::exec('git branch -d test', $sitePath);

        $this->commitAsserter->reset();

        self::$wpAutomation->runWpCliCommand('vp', 'rollback', [$commitHash]);

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCleanWorkingDirectory();
        $this->commitAsserter->assertCountOfAffectedFiles(2);
        $this->commitAsserter->assertCommitPath('D', '%vpdb%/options/vp/vp_option_master.ini');
        $this->commitAsserter->assertCommitPath('D', '%vpdb%/options/vp/vp_option_test.ini');
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Undo to the same state should not do anything
     */
    public function undoToTheSameStateDoesNothing()
    {
        self::$worker->prepare_undoToTheSameState();

        $this->commitAsserter->reset();

        self::$worker->undoSecondCommit();

        $this->commitAsserter->assertNumCommits(0);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Rollback to the same state should not do anything
     */
    public function rollbackToTheSameStateDoesNothing()
    {
        self::$worker->prepare_rollbackToTheSameState();

        $this->commitAsserter->reset();

        self::$worker->rollbackToTheSameState();

        $this->commitAsserter->assertNumCommits(0);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Undo multiple commits should create one commit
     */
    public function undoMultipleCommitsCreatesOneCommit()
    {
        $changes = self::$worker->prepare_undoMultipleCommits();
        $this->commitAsserter->reset();

        self::$worker->undoMultipleCommits();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction('versionpress/undo');
        $this->commitAsserter->assertCountOfAffectedFiles(count($changes));
        $this->commitAsserter->assertCommitPaths($changes);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     */
    public function undoMultipleCommitsDetectsMissingReferencesCorrectly()
    {
        $changes = self::$worker->prepare_undoMultipleDependentCommits();
        $this->commitAsserter->reset();

        self::$worker->undoMultipleDependentCommits();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction('versionpress/undo');
        $this->commitAsserter->assertCountOfAffectedFiles(count($changes));
        $this->commitAsserter->assertCommitPaths($changes);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Undo multiple commits should do nothing id one change cannot be reverted
     */
    public function undoMultipleCommitsDoesNothingIfOneChangeCannotBeReverted()
    {
        self::$worker->prepare_undoMultipleCommitsThatCannotBeReverted();

        $this->commitAsserter->reset();

        self::$worker->undoMultipleCommitsThatCannotBeReverted();
        $this->commitAsserter->assertNumCommits(0);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Non-DB change doesn't break the database
     */
    public function undoOfNonDbChangeDoesntBreakDatabase()
    {
        $changes = self::$worker->prepare_undoNonDbChange();

        $this->commitAsserter->reset();

        self::$worker->undoNonDbChange();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction('versionpress/undo');
        $this->commitAsserter->assertCountOfAffectedFiles(count($changes));
        $this->commitAsserter->assertCommitPaths($changes);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }
}
