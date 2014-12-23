<?php


class UploadsTest extends SeleniumTestCase {

    /**
     * @test
     * @testdox Uploading file creates 'post/create' action
     */
    public function uploadingFileCreatesPostCreateAction() {
        $commitAsserter = new CommitAsserter($this->gitRepository);

        // Couldn't find out how to automate the default (and more modern) upload.php so let's go via media-new.php
        // see http://stackoverflow.com/q/27607453/21728
        $this->url('wp-admin/media-new.php');
        if (!$this->byCssSelector('#html-upload')->displayed()) {
            $this->byCssSelector('.upload-flash-bypass a')->click();
        }
        $this->byCssSelector('#async-upload')->value(__DIR__ . DIRECTORY_SEPARATOR . 'test.png'); // separator is actually OS-specific here
        $this->byCssSelector('#html-upload')->click();

        $this->waitForElement('.thumbnail', 3000);

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/create");
        $commitAsserter->assertCommitTag("VP-Post-Type", "attachment");
        $commitAsserter->assertCommitPath("A", "wp-content/uploads/*");
        $commitAsserter->assertCleanWorkingDirectory();

    }

    /**
     * @test
     * @testdox Editing file name creates 'post/edit' action
     * @depends uploadingFileCreatesPostCreateAction
     */
    public function editingFileNameCreatesPostEditAction() {
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->byCssSelector('.attachment:first-child .thumbnail')->click(); // click must be on the .thumbnail element
        $this->waitForElement('.edit-attachment-frame', 300);
        $this->setValue('.setting[data-setting=title] input', 'updated image title');
        $this->keys(PHPUnit_Extensions_Selenium2TestCase_Keys::TAB); // focus out, AJAX saves the image
        usleep(500*1000);

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/edit");
        $commitAsserter->assertCommitTag("VP-Post-Type", "attachment");
        $commitAsserter->assertCleanWorkingDirectory();

    }

    /**
     * @test
     * @testdox Deleting file creates 'post/delete' action
     * @depends editingFileNameCreatesPostEditAction
     */
    public function deletingFileCreatesPostDeleteAction() {
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->byCssSelector('.delete-attachment')->click();
        $this->acceptAlert();
        usleep(1000*1000);
        $this->waitForElement('#wp-media-grid');

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("post/delete");
        $commitAsserter->assertCommitTag("VP-Post-Type", "attachment");
        $commitAsserter->assertCommitPath("D", "wp-content/uploads/*");
        $commitAsserter->assertCleanWorkingDirectory();

    }

}
