<?php

namespace VersionPress\Tests\End2End\Translations;

use VersionPress\Tests\End2End\Utils\End2EndTestCase;
use VersionPress\Tests\Utils\CommitAsserter;
use VersionPress\Tests\Utils\DBAsserter;

class TranslationsTest extends End2EndTestCase
{

    /** @var ITranslationsTestWorker */
    private static $worker;

    /**
     * @test
     * @testdox Switching language creates 'translation/activate' action
     */
    public function switchingLanguageCreatesLanguageActivateAction()
    {
        self::$worker->prepare_switchLanguage();

        $this->commitAsserter->reset();

        self::$worker->switchLanguage();

        $this->commitAsserter->assertNumCommits(1);
        $this->commitAsserter->assertCommitAction('translation/activate');
        $this->commitAsserter->assertCommitPath(['A', 'M'], '%vpdb%/options/*');
        $this->commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }
}
