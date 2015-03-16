<?php

namespace VersionPress\Tests\End2End\Media;

use VersionPress\Tests\End2End\Utils\End2EndTestCase;
use VersionPress\Tests\Utils\CommitAsserter;

class MediaTest extends End2EndTestCase {

    /** @var IMediaTestWorker */
    private static $worker;

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        self::$worker->setUploadedFilePath(realpath(__DIR__ . '/../test-data/test.png'));
    }

    /**
     * @test
     * @testdox Uploading file creates 'post/create' action
     */
    public function uploadingFileCreatesPostCreateAction() {
        self::$worker->prepare_uploadFile();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->uploadFile();

        $commitAsserter->ignoreCommits(array("usermeta/create", "usermeta/edit"));

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/create");
        $commitAsserter->assertCommitTag("VP-Post-Type", "attachment");
        $commitAsserter->assertCommitPath("A", "wp-content/uploads/*");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Editing file name creates 'post/edit' action
     * @depends uploadingFileCreatesPostCreateAction
     */
    public function editingFileNameCreatesPostEditAction() {
        self::$worker->prepare_editFileName();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->editFileName();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/edit");
        $commitAsserter->assertCommitTag("VP-Post-Type", "attachment");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Deleting file creates 'post/delete' action
     * @depends editingFileNameCreatesPostEditAction
     */
    public function deletingFileCreatesPostDeleteAction() {
        self::$worker->prepare_deleteFile();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->deleteFile();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/delete");
        $commitAsserter->assertCommitTag("VP-Post-Type", "attachment");
        $commitAsserter->assertCommitPath("D", "wp-content/uploads/*");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }
}
