<?php

namespace VersionPress\Tests\End2End\Revert;

use VersionPress\Tests\End2End\Utils\ITestWorker;

interface IRevertTestWorker extends ITestWorker {

    public function prepare_undoLastCommit();
    public function undoLastCommit();

    public function prepare_undoSecondCommit();
    public function undoSecondCommit();

    public function prepare_undoRevertedCommit();

    public function prepare_tryRestoreEntityWithMissingReference();
    public function tryRestoreEntityWithMissingReference();

    public function prepare_rollbackMoreChanges();
    public function rollbackMoreChanges();

    public function prepare_clickOnCancel();
    public function clickOnCancel();

    public function prepare_undoWithNotCleanWorkingDirectory();
    
    public function prepare_undoMultipleCommits();
    public function undoMultipleCommits();

    public function prepare_undoMultipleCommitsThatCannotBeReverted();
    public function undoMultipleCommitsThatCannotBeReverted();
}
