<?php

namespace VersionPress\Tests\End2End\Widgets;

use Exception;
use VersionPress\Tests\End2End\Utils\End2EndTestCase;
use VersionPress\Tests\Utils\CommitAsserter;
use VersionPress\Tests\Utils\DBAsserter;
use VersionPress\Tests\Utils\WpVersionComparer;

class WidgetsTest extends End2EndTestCase
{

    /** @var IWidgetsTestWorker */
    private static $worker;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::clearSidebars();
        try {
            self::$wpAutomation->deleteOption('widget_calendar');
        } catch (Exception $e) {
            // It's ok. This option does not exist in the clear WP.
        }
    }

    /**
     * @test
     * @testdox Creating first widget of given type creates new option
     */
    public function creatingFirstWidgetOfGivenTypeCreatesOption()
    {
        self::$worker->prepare_createWidget();

        $this->commitAsserter->reset();

        self::$worker->createWidget();

        $this->commitAsserter->assertNumCommits(1);

        if (self::$testConfig->end2endTestType === 'selenium' &&
            WpVersionComparer::compare(self::$testConfig->testSite->wpVersion, '4.4-beta1') >= 0
        ) {
            $this->commitAsserter->assertCommitAction('option/edit');
            $this->commitAsserter->assertCommitPath('M', '%vpdb%/options/%VPID%.ini');
        } else {
            $this->commitAsserter->assertCommitAction('option/create');
            $this->commitAsserter->assertCommitPath('A', '%vpdb%/options/%VPID%.ini');
        }

        $this->commitAsserter->assertCountOfAffectedFiles(2);
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Creating second widget of given type updates the option
     * @depends creatingFirstWidgetOfGivenTypeCreatesOption
     */
    public function creatingSecondWidgetOfGivenTypeUpdatesOption()
    {
        self::$worker->prepare_createWidget();

        $this->commitAsserter->reset();

        self::$worker->createWidget();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction('option/edit');
        $this->commitAsserter->assertCountOfAffectedFiles(2);
        $this->commitAsserter->assertCommitPath('M', '%vpdb%/options/%VPID%.ini');
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Editing widget creates 'option/edit' action.
     */
    public function editingWidgetCreatesOptionEditAction()
    {
        self::$worker->prepare_editWidget();

        $this->commitAsserter->reset();

        self::$worker->editWidget();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction('option/edit');
        $this->commitAsserter->assertCountOfAffectedFiles(1);
        $this->commitAsserter->assertCommitPath('M', '%vpdb%/options/%VPID%.ini');
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    /**
     * @test
     * @testdox Deleting widget creates 'option/edit' action
     */
    public function deletingWidgetCreatesOptionEditAction()
    {
        self::$worker->prepare_deleteWidget();

        $this->commitAsserter->reset();

        self::$worker->deleteWidget();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction('option/edit');
        $this->commitAsserter->assertCountOfAffectedFiles(2);
        $this->commitAsserter->assertCommitPath('M', '%vpdb%/options/%VPID%.ini');
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }

    private static function clearSidebars()
    {
        $sidebars = self::$wpAutomation->getSidebars();
        if (count($sidebars) == 0) {
            self::fail('Tests can be run only with theme with sidebars');
        }
        foreach ($sidebars as $sidebar) {
            $widgets = self::$wpAutomation->getWidgets($sidebar);
            self::$wpAutomation->deleteWidgets($widgets);
        }
    }
}
