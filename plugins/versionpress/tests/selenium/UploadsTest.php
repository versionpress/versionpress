<?php


class UploadsTest extends SeleniumTestCase {

    /**
     * @test
     */
    public function addNewFile() {
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

}
