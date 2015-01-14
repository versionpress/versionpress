<?php

class WidgetTest extends SeleniumTestCase {

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        self::clearSidebars();
        WpAutomation::deleteOption('widget_calendar');
    }

    /**
     * @test
     * @testdox Creating first widget of given type creates new option
     */
    public function creatingFirstWidgetOfGivenTypeCreatesOption() {
        $this->url('wp-admin/widgets.php');
        $this->jsClick("#widget-list .widget:contains('Calendar') .widget-control-edit");
        $this->waitAfterRedirect();
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->byCssSelector("form[action='widgets.php'] input[name*=title]")->value('Some widget');
        $this->byCssSelector("form[action='widgets.php'] input[type=submit]")->click();
        $this->waitAfterRedirect();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction('option/create');
        $commitAsserter->assertCountOfAffectedFiles(1);
        $commitAsserter->assertCommitPath('M', '%vpdb%/options.ini');
        $commitAsserter->assertCleanWorkingDirectory();
    }

    private static function clearSidebars() {
        $sidebars = WpAutomation::getSidebars();
        if (count($sidebars) == 0) {
            self::fail('Tests can be run only with theme with sidebars');
        }
        foreach ($sidebars as $sidebar) {
            $widgets = WpAutomation::getWidgets($sidebar);
            WpAutomation::deleteWidgets($widgets);
        }
    }
}