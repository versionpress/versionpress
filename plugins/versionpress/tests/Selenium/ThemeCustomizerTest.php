<?php

namespace VersionPress\Tests\Selenium;

use VersionPress\Tests\Utils\CommitAsserter;

class ThemeCustomizerTest extends SeleniumTestCase
{
    /**
     * @test
     * @testdox Every change made in customizer creates 'theme/customize' action
     */
    public function everyChangeMadeInCustomizerCreatesThemeCustomizeAction()
    {
        $this->url(self::$wpAdminPath . '/customize.php');
        $this->byCssSelector('#accordion-section-title_tagline .accordion-section-title')->click();
        $this->setValue('#customize-control-blogname input', 'Some name');
        $this->byId('save')->click();
        $this->waitForAjax();

        $commitAsserter = new CommitAsserter($this->gitRepository);
        $this->setValue('#customize-control-blogname input', 'Blogname from customizer');
        $this->byId('save')->click();
        $this->waitForAjax();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction('option/edit');
        $commitAsserter->assertCleanWorkingDirectory();
    }
}
