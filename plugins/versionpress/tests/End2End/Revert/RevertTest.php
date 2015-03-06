<?php

namespace VersionPress\Tests\End2End\Revert;

use VersionPress\Tests\End2End\Utils\End2EndTestCase;
use VersionPress\Tests\Utils\CommitAsserter;

class RevertTest extends End2EndTestCase {

    /** @var IRevertTestWorker */
    private static $worker;

    /**
     * @test
     * @testdox Undo reverts changes in given commit
     */
    public function undoRevertChangesInGivenCommit() {
        self::$worker->prepare_undoLastCommit();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->undoLastCommit();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction('versionpress/undo');
        $commitAsserter->assertCountOfAffectedFiles(1);
        $commitAsserter->assertCommitPath('D', '%vpdb%/posts/*');
        $commitAsserter->assertCleanWorkingDirectory();
    }

    /**
     * @test
     * @testdox Undo reverts only one commit
     */
    public function undoRevertsOnlyOneCommit() {
        self::$worker->prepare_undoSecondCommit();
        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->undoSecondCommit();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction('versionpress/undo');
        $commitAsserter->assertCountOfAffectedFiles(1);
        $commitAsserter->assertCommitPath('M', '%vpdb%/options.ini');
        $commitAsserter->assertCleanWorkingDirectory();
    }

    /**
     * @test
     * @testdox Undo commit can be also reverted.
     */
    public function undoCommitCanBeAlsoReverted() {
        self::$worker->prepare_undoRevertedCommit();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->undoLastCommit();
        self::$worker->undoLastCommit();

        $commitAsserter->assertNumCommits(2);
        $commitAsserter->assertCommitAction('versionpress/undo');
        $commitAsserter->assertCountOfAffectedFiles(1);
        $commitAsserter->assertCommitPath('A', '%vpdb%/posts/*');
        $commitAsserter->assertCleanWorkingDirectory();
    }

    /**
     * @test
     * @testdox Comment deletion cannot be reverted if the commented post no longer exists
     */
    public function entityWithMissingReferenceCannotBeRestoredWithRevert() {
        self::$worker->prepare_tryRestoreEntityWithMissingReference();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->tryRestoreEntityWithMissingReference();
        $commitAsserter->assertNumCommits(0);
        $commitAsserter->assertCleanWorkingDirectory();

    }

    /**
     * @test
     * @testdox Rollback reverts all changes made after chosen commit
     */
    public function rollbackRevertsAllChangesMadeAfterChosenCommit() {
        self::$worker->prepare_rollbackMoreChanges();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->rollbackMoreChanges();
        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction('versionpress/rollback');
        $commitAsserter->assertCountOfAffectedFiles(3);
        $commitAsserter->assertCommitPath('D', '%vpdb%/posts/*');
        $commitAsserter->assertCommitPath('D', '%vpdb%/comments/*');
        $commitAsserter->assertCommitPath('M', '%vpdb%/options.ini');
        $commitAsserter->assertCleanWorkingDirectory();
    }

    /**
     * @test
     * @testdox Clicking on Cancel only hides the popup
     */
    public function clickingOnCancelOnlyHidesThePopup() {
        self::$worker->prepare_clickOnCancel();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->clickOnCancel();

        $commitAsserter->assertNumCommits(0);
        $commitAsserter->assertCleanWorkingDirectory();
    }

    /**
     * @test
     * @testdox OK button is disabled if the working directory is not clean
     */
    public function undoDoesNothingIfTheWorkingDirectoryIsNotClean() {
        self::$worker->prepare_undoWithNotCleanWorkingDirectory();

        $commitAsserter = new CommitAsserter($this->gitRepository);
        touch(self::$testConfig->testSite->path . '/revert-test-file');

        self::$worker->undoLastCommit();

        $commitAsserter->assertNumCommits(0);
        unlink(self::$testConfig->testSite->path . '/revert-test-file');
        $commitAsserter->assertCleanWorkingDirectory();
    }
}
