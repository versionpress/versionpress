<?php

namespace VersionPress\Tests\Utils;

use PHPUnit_Framework_Assert;

class MergeAsserter
{

    /**
     * Asserts that a merge from $fromBranch ends with a conflict
     *
     * @param string $mergeCommand Merge command to run and assert on
     */
    public static function assertMergeConflict($mergeCommand)
    {

        $exitCode = MergeDriverTestUtils::runGitCommand($mergeCommand);

        if ($exitCode == 0) {
            PHPUnit_Framework_Assert::fail('Expected merge conflict, got clean merge instead');
        } else {
            if ($exitCode != 1) {
                PHPUnit_Framework_Assert::fail('Merge ended with an unexpected exit code ' . $exitCode);
            }
        }

    }

    /**
     * Asserts that a merge from $fromBranch ends with a clean merge (not a conflict)
     *
     * @param string $mergeCommand Merge command to run and assert on
     */
    public static function assertCleanMerge($mergeCommand)
    {
        $exitCode = MergeDriverTestUtils::runGitCommand($mergeCommand);

        if ($exitCode == 1) {
            PHPUnit_Framework_Assert::fail('Merge ended with a conflict, clean merge was expected');
        } else {
            if ($exitCode != 0) {
                PHPUnit_Framework_Assert::fail('Merge ended with an unexpected exit code ' . $exitCode);
            }
        }

    }
}
