<?php

namespace VersionPress\Tests\End2End\Options;

use VersionPress\Tests\End2End\Utils\End2EndTestCase;
use VersionPress\Tests\Utils\CommitAsserter;
use VersionPress\Tests\Utils\DBAsserter;

class OptionsTest extends End2EndTestCase
{

    /** @var IOptionsTestWorker */
    private static $worker;

    /**
     * @test
     * @testdox Changing option creates 'option/update' action
     */
    public function changingOptionCreatesOptionEditAction()
    {
        self::$worker->prepare_changeOption();

        $this->commitAsserter->reset();

        self::$worker->changeOption();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction('option/update');
        $this->commitAsserter->assertCommitPath('M', '%vpdb%/options/%VPID%.ini');
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Changing more option creates bulk 'option/update' action
     */
    public function changingMoreOptionsCreatesOptionEditAction()
    {
        self::$worker->prepare_changeTwoOptions();

        $this->commitAsserter->reset();

        self::$worker->changeTwoOptions();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertBulkAction('option/update', 2);
        $this->commitAsserter->assertCommitPath('M', '%vpdb%/options/%VPID%.ini');
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }
}
