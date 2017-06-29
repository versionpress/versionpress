<?php

namespace VersionPress\Tests\End2End\Media;

use VersionPress\Tests\End2End\Utils\End2EndTestCase;
use VersionPress\Tests\Utils\CommitAsserter;
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

        $this->commitAsserter->reset();

        self::$worker->uploadFile();

        $this->commitAsserter->ignoreCommits(["usermeta/create", "usermeta/update"]);

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("post/create");
        $this->commitAsserter->assertCommitTag("VP-Post-Type", "attachment");
        $this->commitAsserter->assertCommitPath("A", "%uploads%/*");
        $this->commitAsserter->assertCleanWorkingDirectory();
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

        $this->commitAsserter->reset();

        self::$worker->editFileName();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("post/update");
        $this->commitAsserter->assertCommitTag("VP-Post-Type", "attachment");
        $this->commitAsserter->assertCleanWorkingDirectory();
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

        $this->commitAsserter->reset();

        self::$worker->deleteFile();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("post/delete");
        $this->commitAsserter->assertCommitTag("VP-Post-Type", "attachment");
        $this->commitAsserter->assertCommitPath("D", "%uploads%/*");
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @depends uploadingFileCreatesPostCreateAction
     */
    public function editationOfFileCreatesCommit()
    {
        self::$worker->prepare_editFile();

        $this->commitAsserter->reset();

        self::$worker->editFile();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction("post/update");
        $this->commitAsserter->assertCommitTag("VP-Post-Type", "attachment");
        $this->commitAsserter->assertCommitPath("A", "%uploads%/*");
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }
}
