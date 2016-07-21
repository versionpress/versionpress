<?php

namespace VersionPress\Tests\Utils;

use Exception;
use Nette\Utils\Strings;
use PHPUnit_Framework_Assert;
use VersionPress\ChangeInfos\BulkChangeInfo;
use VersionPress\ChangeInfos\ChangeInfoEnvelope;
use VersionPress\ChangeInfos\ChangeInfoMatcher;
use VersionPress\ChangeInfos\TrackedChangeInfo;
use VersionPress\ChangeInfos\UntrackedChangeInfo;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Git\ActionsInfo;
use VersionPress\Git\Commit;

/**
 * A short-lived object with lifespan of one test method that stores the most recent
 * commit on creation and then asserts that there were expected number of commits since then,
 * of certain type etc.
 *
 * Can be used from any type of tests (Selenium, WP-CLI, ...).
 */
class CommitAsserter
{


    /**
     * When this object is created, a reference to most recent commit at that time
     * is stored in this variable so that asserts can only work with the newly created commits.
     * @var Commit
     */
    private $startCommit;
    private $gitRepository;

    /**
     * Various asserts have a $whichCommit parameter of type int that accepts zero, -1, -2 etc.
     * It was introduced to avoid concrete hashes in argument values as that would be a problem
     * for test code in itself. In this numeric system zero simply points to the most recent commit,
     * -1 to the one next to it etc. You can visualize it like this:
     *
     *       9b85e18 (HEAD)  <=   0
     *       436f828         <=  -1
     *       6b9ca72         <=  -2
     *       d16dffd         <=  -3
     *
     * (This member is here just to document various "whichCommit" parameters, is not actually used).
     *
     * @var int
     */
    private $whichCommitParameter;

    private $pathPlaceholders;
    /** @var DbSchemaInfo */
    private $dbSchema;
    /** @var ActionsInfo */
    private $actionsInfo;

    /**
     * Create CommitAsserter to start tracking the git repo for future asserts. Should generally
     * be called after a test setup (if there is any) and before all the actual work. Asserts follow
     * after it.
     *
     * @param \VersionPress\Git\GitRepository $gitRepository
     * @param DbSchemaInfo $dbSchema
     * @param ActionsInfo $actionsInfo
     * @param string[] $pathPlaceholders
     */
    public function __construct($gitRepository, DbSchemaInfo $dbSchema, ActionsInfo $actionsInfo, $pathPlaceholders = [])
    {
        $this->gitRepository = $gitRepository;
        $this->pathPlaceholders = $pathPlaceholders;
        $this->dbSchema = $dbSchema;
        $this->actionsInfo = $actionsInfo;

        if ($gitRepository->isVersioned()) {
            $this->startCommit = $gitRepository->getCommit($gitRepository->getLastCommitHash());
        }
    }


    public function reset()
    {
        $this->startCommit = $this->gitRepository->getCommit($this->gitRepository->getLastCommitHash());
        $this->ignoreCommitsWithActions = [];


    }

    //---------------------------
    // Pre-assertion setup
    //---------------------------

    private $ignoreCommitsWithActions = [];

    /**
     * Ignores commits of given action(s)
     *
     * This is useful in tests where different number of commits might be created in different circumstances.
     * For example, file upload will create two commits on first attempted upload ('post/create'
     * and 'usermeta/edit') while it will only generate a single commit ('post/create') for repeated
     * runs. In such case, if we only care about the 'post/create' action, 'usermeta/edit' can be set as ignored
     * using this method.
     *
     * @param string|string[] $action An action like "usermeta/edit", or an array of them
     */
    public function ignoreCommits($action)
    {
        $this->ignoreCommitsWithActions = (array)$action;
    }



    //---------------------------
    // Assertions
    //---------------------------

    /**
     * Asserts that the number of commits made since the constructor matches the given number.
     *
     * @param int $numExpectedCommits
     */
    public function assertNumCommits($numExpectedCommits)
    {
        $commits = $this->getNonIgnoredCommits();
        $numActualCommits = count($commits);
        if ($numExpectedCommits !== $numActualCommits) {
            PHPUnit_Framework_Assert::fail(
                "There were $numActualCommits commit(s) after {$this->startCommit->getShortHash()} " .
                "while we expected $numExpectedCommits"
            );
        }
    }


    /**
     * Asserts that the recorded commit if of certain type, e.g. "post/edit". By default inspects
     * the most recent commit; if this asserter captured more commits $whichCommit specifies
     * which commit to assert against.
     *
     * @see $whichCommitParameter
     *
     * @param string $expectedAction Expected action, e.g., "post/edit" or "wordpress/update".
     * @param int $whichCommit See $whichCommitParameter documentation. "HEAD" by default.
     * @param bool $regardlessOfPriority By default, commit action must be the "main" one in the envelope
     *   (with the highest priority). If this param is set to true the whole envelope is searched for
     *   the given action.
     */
    public function assertCommitAction($expectedAction, $whichCommit = 0, $regardlessOfPriority = false)
    {
        $commit = $this->getCommit($whichCommit);
        $changeInfo = $this->getChangeInfo($commit);

        if ($regardlessOfPriority) {
            $changeInfoContainsAction = ChangeInfoUtils::containsAction($changeInfo, $expectedAction);
            if (!$changeInfoContainsAction) {
                PHPUnit_Framework_Assert::fail(
                    "Action '$expectedAction' not found in commit {$commit->getShortHash()}"
                );
            }
        } else {
            $commitAction = ChangeInfoUtils::getFullAction($changeInfo);

            if ($expectedAction != $commitAction) {
                PHPUnit_Framework_Assert::fail(
                    "Expected action '$expectedAction' but was '$commitAction' in commit {$commit->getShortHash()}"
                );
            }
        }

    }

    /**
     * Asserts that commit is a bulk action. Asserts the action and number of grouped change info objects.
     *
     * @param string $expectedAction Expected action, e.g., "post/edit" or "plugin/activate".
     * @param int $expectedCountOfGroupedActions
     */
    public function assertBulkAction($expectedAction, $expectedCountOfGroupedActions)
    {
        $commit = $this->getCommit(0);
        $changeInfoEnvelope = $this->getChangeInfo($commit);

        $changeInfoContainsAction = ChangeInfoUtils::containsAction($changeInfoEnvelope, $expectedAction);
        if (!$changeInfoContainsAction) {
            PHPUnit_Framework_Assert::fail("Action '$expectedAction' not found in commit {$commit->getShortHash()}");
        }

        $changeInfoList = $changeInfoEnvelope->getReorganizedInfoList();
        $firstChangeInfo = $changeInfoList[0];

        if (!($firstChangeInfo instanceof BulkChangeInfo)) {
            PHPUnit_Framework_Assert::fail("Commit is not a bulk action");
        }

        if (ChangeInfoUtils::getFullAction($firstChangeInfo) !== $expectedAction) {
            PHPUnit_Framework_Assert::fail(
                "Bulk action '{$firstChangeInfo->getAction()}' expected to be '{$expectedAction}'"
            );
        }

        $countOfGroupedActions = count($firstChangeInfo->getChangeInfos());
        if ($countOfGroupedActions !== $expectedCountOfGroupedActions) {
            PHPUnit_Framework_Assert::fail(
                "Expected that bulk action contains $expectedCountOfGroupedActions actions " .
                "instead of $countOfGroupedActions."
            );
        }
    }

    /**
     * Asserts that two commits are "equivalent". Equivalent means that they both captured the same
     * action over the same entity and that the captured VP tags are the same set (values may differ).
     *
     * When called without parameters, compares HEAD and the next most recent commit.
     *
     * @see $whichCommitParameter
     *
     * @param int $whichCommit See $whichCommitParameter documentation. "HEAD" by default.
     * @param int $referenceCommit See $whichCommitParameter documentation. "HEAD^" by default.
     */
    public function assertCommitsAreEquivalent($whichCommit = 0, $referenceCommit = -1)
    {
        $commit = $this->getCommit($whichCommit);
        $referenceCommit = $this->getCommit($referenceCommit);

        $commitChangeInfo = $this->getChangeInfo($commit);
        $referenceCommitChangeInfo = $this->getChangeInfo($referenceCommit);

        if (!($commitChangeInfo instanceof TrackedChangeInfo || $commitChangeInfo instanceof ChangeInfoEnvelope) ||
            !($referenceCommitChangeInfo instanceof TrackedChangeInfo
                || $referenceCommitChangeInfo instanceof ChangeInfoEnvelope)) {
            PHPUnit_Framework_Assert::fail(
                "Sorry, this assertion is only available for TrackedChangedInfo or ChangeInfoEnvelope commits"
            );
        }

        /** @var TrackedChangeInfo $commitChangeInfo */
        /** @var TrackedChangeInfo $referenceCommitChangeInfo */

        if (!ChangeInfoUtils::captureSameAction($commitChangeInfo, $referenceCommitChangeInfo)) {
            PHPUnit_Framework_Assert::fail(
                "Commit " . $commit->getHash() . " does not capture the same action as reference commit " .
                $referenceCommit->getHash()
            );
        }
    }

    public function assertCommitTag($tagKey, $tagValue)
    {
        $changeInfo = $this->getChangeInfo($this->getCommit());
        $foundCustomTagValue = ChangeInfoUtils::getCustomTagValue($changeInfo, $tagKey);

        if (!$foundCustomTagValue) {
            PHPUnit_Framework_Assert::fail("VP tag " . $tagKey . " not found on created commit");
        }

        if ($foundCustomTagValue !== $tagValue) {
            PHPUnit_Framework_Assert::fail("Expected: '$tagKey: $tagValue', Actual: '$tagKey: $foundCustomTagValue'");
        }
    }

    /**
     * Asserts that commit affected some path. Paths support wildcards and two placeholders:
     *
     *  - %vpdb% expands to "wp-content/vpdb"
     *  - %VPID% expands to the VPID of the committed entity
     *  - %VPID(TAG_NAME)% expands to VPID stored in given TAG_NAME
     *
     * Placeholders are case sensitive.
     *
     * @param string $type Standard git "M" (modified), "A" (added), "D" (deleted) etc.
     *                     or array of actions for more possibilities.
     * @param string $path Path relative to repo root. Supports wildcards, e.g. "wp-content/uploads/*",
     *                     and placeholders, e.g., "%vpdb%/posts/%VPID%.ini"
     * @param int $whichCommit See $whichCommitParameter documentation. "HEAD" by default.
     */
    public function assertCommitPath($type, $path, $whichCommit = 0)
    {
        $revRange = $this->getRevRange($whichCommit);
        $path = $this->expandPath($path, $whichCommit);
        $affectedFiles = $this->gitRepository->getModifiedFilesWithStatus($revRange);
        $matchingPaths = array_filter($affectedFiles, function ($item) use ($type, $path) {
            return in_array($item["status"], (array)$type) && fnmatch($path, $item["path"]);
        });
        if (count($matchingPaths) == 0) {
            PHPUnit_Framework_Assert::fail("Commit didn't affect path '$path' with change of type '$type'");
        }
    }

    /**
     * Calls {@link assertCommitPath} for every item of given array.
     * The first item in the nested array equals the type of change, the second one is the changed path.
     *
     * @param $changes
     * @param int $whichCommit See $whichCommitParameter documentation. "HEAD" by default.
     */
    public function assertCommitPaths($changes, $whichCommit = 0)
    {
        foreach ($changes as $change) {
            $this->assertCommitPath($change[0], $change[1], $whichCommit);
        }
    }

    /**
     * Asserts that commit affected exact number of files (no matter the type).
     *
     * @param int $count Expected count of affected files.
     * @param int $whichCommit See $whichCommitParameter documentation. "HEAD" by default.
     */
    public function assertCountOfAffectedFiles($count, $whichCommit = 0)
    {
        $revRange = $this->getRevRange($whichCommit);
        $affectedFiles = $this->gitRepository->getModifiedFilesWithStatus($revRange);
        $countOfAffectedFiles = count($affectedFiles);
        if ($countOfAffectedFiles != $count) {
            $adverb = $countOfAffectedFiles < $count ? "less" : "more";
            PHPUnit_Framework_Assert::fail(
                "Commit affected $adverb files ($countOfAffectedFiles) then expected ($count)"
            );
        }
    }

    public function assertCountOfUntrackedFiles($count)
    {
        $gitStatus = $this->gitRepository->getStatus(true);
        $countOfUntrackedFiles = count($gitStatus);
        if ($countOfUntrackedFiles != $count) {
            $adverb = $countOfUntrackedFiles < $count ? "less" : "more";
            PHPUnit_Framework_Assert::fail(
                "In the working directory is $adverb untracked files ($countOfUntrackedFiles) then expected ($count)"
            );
        }
    }

    public function assertCleanWorkingDirectory()
    {
        $gitStatus = $this->gitRepository->getStatus();
        if (!empty($gitStatus)) {
            PHPUnit_Framework_Assert::fail("Expected clean working directory but got:\n$gitStatus");
        }
    }



    //---------------------------
    // Helper methods
    //---------------------------

    /**
     * Cache for getNonIgnoredCommits(), don't use directly
     */
    private $commitCache;

    /**
     * Use this to fetch all the commits since $startCommit that are not ignored.
     *
     * @return Commit[]
     */
    private function getNonIgnoredCommits()
    {
        if (!$this->commitCache) {
            $commits = $this->gitRepository->log("{$this->startCommit->getHash()}..HEAD");
            if ($this->ignoreCommitsWithActions) {
                $commits = array_filter($commits, function ($commit) {
                    $changeInfo = $this->getChangeInfo($commit);
                    return $changeInfo instanceof UntrackedChangeInfo
                    || !in_array(ChangeInfoUtils::getFullAction($changeInfo), $this->ignoreCommitsWithActions);
                });
            }
            $this->commitCache = array_values($commits); // array_values reindexes the array from zero
        }
        return $this->commitCache;
    }


    /**
     * @param int $whichCommit See $whichCommitParameter documentation. The most recent commit by default.
     * @return Commit
     */
    private function getCommit($whichCommit = 0)
    {
        $nonIgnoredCommits = $this->getNonIgnoredCommits();
        $index = abs($whichCommit);
        if (isset($nonIgnoredCommits[$index])) {
            return $nonIgnoredCommits[$index];
        } else {
            $fromRev = "HEAD~" . ($index + 1);
            $toRev = "HEAD~" . $index;
            $commits = $this->gitRepository->log("$fromRev..$toRev");
            return $commits[0];
        }
    }

    /**
     * @param Commit $commit
     * @return ChangeInfoEnvelope|UntrackedChangeInfo
     */
    protected function getChangeInfo($commit)
    {
        return ChangeInfoEnvelope::buildFromCommitMessage($commit->getMessage(), $this->dbSchema, $this->actionsInfo);
    }


    /**
     * Converts $whichCommit (int) to a Git rev range
     *
     * @param int $whichCommit
     * @return string
     */
    private function getRevRange($whichCommit)
    {

        $nonIgnoredCommits = $this->getNonIgnoredCommits();
        $commit = $nonIgnoredCommits[abs($whichCommit)];
        // will fail if commit is the first one but that should never happen
        $revRange = "{$commit->getHash()}^..{$commit->getHash()}";

        return $revRange;
    }


    /**
     * @param $path
     * @param $whichCommit
     * @return mixed
     * @throws Exception
     */
    private function expandPath($path, $whichCommit)
    {
        foreach ($this->pathPlaceholders as $placeholder => $value) {
            $path = str_replace("%$placeholder%", $value, $path);
        }

        $containsVpId = Strings::contains($path, "%VPID%");
        $containsVpTag = Strings::contains($path, "%VPID(");

        if ($containsVpId || $containsVpTag) {
            $changeInfo = $this->getChangeInfo($this->getCommit($whichCommit));

            if ($containsVpId) {
                $vpid = ChangeInfoUtils::getVpid($changeInfo);
                $vpidPath = Strings::substring($vpid, 0, 2) . '/' . $vpid;
                $path = str_replace("%VPID%", $vpidPath, $path);
            }

            if ($containsVpTag) {
                $commitMessage = $changeInfo->getCommitMessage();
                $path = preg_replace_callback('/(.*)%VPID\((.*)\)%(.*)/', function ($parts) use ($commitMessage) {
                    $vpid = $commitMessage->getVersionPressTag($parts[2]);
                    $vpidPath = Strings::substring($vpid, 0, 2) . '/' . $vpid;

                    return $parts[1] . $vpidPath . $parts[3];
                }, $path);
            }
        }

        return $path;
    }
}
