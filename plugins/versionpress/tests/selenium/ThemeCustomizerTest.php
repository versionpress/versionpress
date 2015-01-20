<?php

class ThemeCustomizerTest extends SeleniumTestCase {
    /**
     * @test
     * @testdox Every change made in customizer creates 'theme/customize' action
     */
    public function everyChangeMadeInCustomizerCreatesThemeCustomizeAction() {
        $this->url('wp-admin/customize.php');
        $this->byCssSelector('#accordion-section-title_tagline .accordion-section-title')->click();
        $this->setValue('#customize-control-blogname input', 'Blogname from customizer');

        $commitAsserter = new CommitAsserter($this->gitRepository);
        $this->byId('save')->click();
        $this->waitForAjax();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction('theme/customize');
        $commitAsserter->assertCleanWorkingDirectory();
    }
}