<?php

class SettingsTest extends SeleniumTestCase {
    /**
     * @test
     * @testdox Changing settings creates 'option/edit' action
     */
    public function changingSettingsCreatesOptionEditAction() {
        $this->loginIfNecessary();
        $this->url('wp-admin/options-general.php');

        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->byCssSelector('#blogname')->value(' edit');
        $this->byCssSelector('#submit')->click();
        $this->waitAfterRedirect();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction('option/edit');
        $commitAsserter->assertCommitPath('M', '%vpdb%/options.ini');
        $commitAsserter->assertCleanWorkingDirectory();
    }

    /**
     * @test
     * @testdox Changing more settings creates 'option/edit' action
     */
    public function changingMoreSettingsCreatesOptionEditAction() {
        $this->loginIfNecessary();
        $this->url('wp-admin/options-general.php');

        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->byCssSelector('#blogname')->value(' edit');
        $this->byCssSelector('#blogdescription')->value(' edit');

        $this->byCssSelector('#submit')->click();
        $this->waitAfterRedirect();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction('option/edit');
        $commitAsserter->assertCommitPath('M', '%vpdb%/options.ini');
        $commitAsserter->assertCleanWorkingDirectory();
    }
}