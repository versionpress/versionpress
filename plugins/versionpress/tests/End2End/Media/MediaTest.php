<?php

namespace VersionPress\Tests\End2End\Media;

use VersionPress\Tests\End2End\Utils\End2EndTestCase;
use VersionPress\Tests\Utils\DBAsserter;

class MediaTest extends End2EndTestCase
{

    /** @var IMediaTestWorker */
    private static $worker;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$worker->setUploadedFilePath(realpath(__DIR__ . '/../test-data/test.png'));
    }

    /**
     * @test
     * @testdox Uploading file creates 'post/create' action
     */
    public function uploadingFileCreatesPostCreateAction()
    {
        self::$worker->prepare_uploadFile();

        $commitAsserter = $this->newCommitAsserter();

        self::$worker->uploadFile();

        $commitAsserter->ignoreCommits(["usermeta/create", "usermeta/update"]);

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/create");
        $commitAsserter->assertCommitTag("VP-Post-Type", "attachment");
        $commitAsserter->assertCommitPath("A", "%uploads%/*");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Editing file name creates 'post/update' action
     * @depends uploadingFileCreatesPostCreateAction
     */
    public function editingFileNameCreatesPostEditAction()
    {
        self::$worker->prepare_editFileName();

        $commitAsserter = $this->newCommitAsserter();

        self::$worker->editFileName();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/update");
        $commitAsserter->assertCommitTag("VP-Post-Type", "attachment");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Deleting file creates 'post/delete' action
     * @depends editingFileNameCreatesPostEditAction
     */
    public function deletingFileCreatesPostDeleteAction()
    {
        self::$worker->prepare_deleteFile();

        $commitAsserter = $this->newCommitAsserter();

        self::$worker->deleteFile();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/delete");
        $commitAsserter->assertCommitTag("VP-Post-Type", "attachment");
        $commitAsserter->assertCommitPath("D", "%uploads%/*");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @depends uploadingFileCreatesPostCreateAction
     */
    public function editationOfFileCreatesCommit()
    {
        self::$worker->prepare_editFile();

        $commitAsserter = $this->newCommitAsserter();

        self::$worker->editFile();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/update");
        $commitAsserter->assertCommitTag("VP-Post-Type", "attachment");
        $commitAsserter->assertCommitPath("A", "%uploads%/*");
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }
}
