<?php

namespace VersionPress\Tests\End2End\Translations;

use VersionPress\Tests\End2End\Utils\End2EndTestCase;
use VersionPress\Tests\Utils\CommitAsserter;
use VersionPress\Tests\Utils\DBAsserter;

class TranslationsTest extends End2EndTestCase {

    /** @var ITranslationsTestWorker */
    private static $worker;

    /**
     * @test
     * @testdox Switching language creates 'translation/switch' action
     */
    public function switchingLanguageCreatesLanguageSwitchAction() {
        self::$worker->prepare_switchLanguage();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$worker->switchLanguage();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction('translation/switch');
        $commitAsserter->assertCommitPath('M', '%vpdb%/options.ini');
        $commitAsserter->assertCleanWorkingDirectory();
        DBAsserter::assertFilesEqualDatabase();
    }
}
