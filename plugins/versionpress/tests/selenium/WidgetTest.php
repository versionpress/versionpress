<?php

class WidgetTest extends SeleniumTestCase {

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        self::clearSidebars();
        try {
            WpAutomation::deleteOption('widget_calendar');
        } catch (Exception $e) {
            // It's ok. This option does not exist in the clear WP.
        }
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

    /**
     * @test
     * @testdox Creating second widget of given type updates the option
     * @depends creatingFirstWidgetOfGivenTypeCreatesOption
     */
    public function creatingSecondWidgetOfGivenTypeUpdatesOption() {
        $this->url('wp-admin/widgets.php');
        $this->jsClick("#widget-list .widget:contains('Calendar') .widget-control-edit");
        $this->waitAfterRedirect();
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->byCssSelector("form[action='widgets.php'] input[name*=title]")->value('Other widget');
        $this->byCssSelector("form[action='widgets.php'] input[type=submit]")->click();
        $this->waitAfterRedirect();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction('option/edit');
        $commitAsserter->assertCountOfAffectedFiles(1);
        $commitAsserter->assertCommitPath('M', '%vpdb%/options.ini');
        $commitAsserter->assertCleanWorkingDirectory();
    }

    /**
     * @test
     * @testdox Editing widget creates 'option/edit' action.
     */
    public function editingWidgetCreatesOptionEditAction() {
        $this->url('wp-admin/widgets.php');
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->jsClick('#widgets-right .widget-control-edit');
        $this->executeScript("jQuery('#widgets-right .widget .widget-inside input[name*=title]').first().val('Edited title')");
        $this->executeScript("jQuery('#widgets-right .widget .widget-inside input[type=submit]').first().click()");
        $this->waitForAjax();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction('option/edit');
        $commitAsserter->assertCountOfAffectedFiles(1);
        $commitAsserter->assertCommitPath('M', '%vpdb%/options.ini');
        $commitAsserter->assertCleanWorkingDirectory();
    }

    /**
     * @test
     * @testdox Deleting widget creates 'option/edit' action
     */
    public function deletingWidgetCreatesOptionEditAction() {
        $this->url('wp-admin/widgets.php');
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->jsClick('#widgets-right .widget-control-edit');
        $this->executeScript("jQuery('#widgets-right .widget .widget-control-remove').first().click()");
        $this->waitForAjax();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction('option/edit');
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