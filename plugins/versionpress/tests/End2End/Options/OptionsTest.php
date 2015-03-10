<?php

namespace VersionPress\Tests\End2End\Options;

use VersionPress\Tests\End2End\Utils\End2EndTestCase;
use VersionPress\Tests\Utils\CommitAsserter;

class OptionsTest extends End2EndTestCase {

    /** @var IOptionsTestWorker */
    private static $worker;

    /**
     * @test
     * @testdox Changing option creates 'option/edit' action
     */
    public function changingOptionCreatesOptionEditAction() {
        self::$worker->prepare_changeOption();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->changeOption();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction('option/edit');
        $commitAsserter->assertCommitPath('M', '%vpdb%/options.ini');
        $commitAsserter->assertCleanWorkingDirectory();
        $this->assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Changing more option creates 'option/edit' action
     */
    public function changingMoreOptionsCreatesOptionEditAction() {
        self::$worker->prepare_changeMoreOptions();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->changeMoreOptions();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction('option/edit');
        $commitAsserter->assertCommitPath('M', '%vpdb%/options.ini');
        $commitAsserter->assertCleanWorkingDirectory();
        $this->assertFilesEqualDatabase();
    }
}