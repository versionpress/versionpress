<?php
use Nette\Utils\Strings;
use VersionPress\ChangeInfos\ChangeInfo;
use VersionPress\ChangeInfos\TrackedChangeInfo;
use VersionPress\Git\Commit;
use VersionPress\Tests\Utils\ChangeInfoUtils;


/**
 * A short-lived object with lifespan of one test method that stores the most recent
 * commit on creation and then asserts that there were expected number of commits since then,
 * of certain type etc.
 *
 * Can be used from any type of tests (Selenium, WP-CLI, ...).
 */
class CommitAsserter {

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
     * (This member is here just to document various "whichCommit" parameters, is not used itself).
     *
     * @var int
     */
    private $whichCommitParameter;

    /**
     * Create CommitAsserter to start tracking the git repo for future asserts
     *
     * @param \VersionPress\Git\GitRepository $gitRepository
     */
    function __construct($gitRepository) {
        $this->gitRepository = $gitRepository;
        $this->startCommit = $gitRepository->getCommit($gitRepository->getLastCommitHash());
    }

    /**
     * Asserts that the number of commits made since the constructor matches the given number
     *
     * @param int $numExpectedCommits
     */
    public function assertNumCommits($numExpectedCommits) {
        $numActualCommits = $this->gitRepository->getNumberOfCommits($this->startCommit->getHash());
        if ($numExpectedCommits !== $numActualCommits) {
            PHPUnit_Framework_Assert::fail("There were $numActualCommits commit(s) while we expected $numExpectedCommits");
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
     */
    public function assertCommitAction($expectedAction, $whichCommit = 0) {
        $commit = $this->getCommit($whichCommit);
        $changeInfo = $this->getChangeInfo($commit);
        $commitAction = ChangeInfoUtils::getFullAction($changeInfo);

        if ($expectedAction != $commitAction) {
            PHPUnit_Framework_Assert::fail("Expected '$expectedAction' but the commit action was '$commitAction'");
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
    public function assertCommitsAreEquivalent($whichCommit = 0, $referenceCommit = -1) {
        $commit = $this->getCommit($whichCommit);
        $referenceCommit = $this->getCommit($referenceCommit);

        $commitChangeInfo = $this->getChangeInfo($commit);
        $referenceCommitChangeInfo = $this->getChangeInfo($referenceCommit);

        if (!($commitChangeInfo instanceof TrackedChangeInfo) || !($referenceCommitChangeInfo instanceof TrackedChangeInfo)) {
            PHPUnit_Framework_Assert::fail("Sorry, this assertion is only available for TrackedChangedInfo commits");
        }

        /** @var TrackedChangeInfo $commitChangeInfo */
        /** @var TrackedChangeInfo $referenceCommitChangeInfo */

        if (!ChangeInfoUtils::captureSameAction($commitChangeInfo, $referenceCommitChangeInfo)) {
            PHPUnit_Framework_Assert::fail("Commit " . $commit->getHash() . " does not capture the same action as reference commit " . $referenceCommit->getHash());
        }
    }

    /**
     * @param int $whichCommit See $whichCommitParameter documentation. "HEAD" by default.
     * @return Commit
     */
    private function getCommit($whichCommit = 0) {
        $revRange = $this->getRevRange($whichCommit);
        $commits = $this->gitRepository->log($revRange);
        return $commits[0];

    }

    /**
     * @param Commit $commit
     * @return ChangeInfo
     */
    public function getChangeInfo($commit) {
        return \VersionPress\ChangeInfos\ChangeInfoMatcher::createMatchingChangeInfo($commit->getMessage());
    }

    public function assertCommitTag($tagKey, $tagValue) {
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
     *
     * Placeholders are case insensitive.
     *
     * @param string $type Standard git "M" (modified), "A" (added), "D" (deleted) etc.
     * @param string $path Path relative to repo root. Supports wildcards, e.g. "wp-content/uploads/*",
     *   and placeholders, e.g., "%vpdb%/posts/%VPID%.ini"
     * @param int $whichCommit See $whichCommitParameter documentation. "HEAD" by default.
     */
    public function assertCommitPath($type, $path, $whichCommit = 0) {
        $revRange = $this->getRevRange($whichCommit);
        $path = $this->expandPath($path, $whichCommit);
        $affectedFiles = $this->gitRepository->getModifiedFilesWithStatus($revRange);
        $matchingPaths = array_filter($affectedFiles, function ($item) use ($type, $path) {
            return $item["status"] == $type && fnmatch($path, $item["path"]);
        });
        if (count($matchingPaths) == 0) {
            PHPUnit_Framework_Assert::fail("Commit didn't affect path '$path' with change of type '$type'");
        }
    }

    /**
     * Asserts that commit affected exact number of files (no matter the type).
     *
     * @param int $count Expected count of affected files.
     * @param int $whichCommit See $whichCommitParameter documentation. "HEAD" by default.
     */
    public function assertCountOfAffectedFiles($count, $whichCommit = 0) {
        $revRange = $this->getRevRange($whichCommit);
        $affectedFiles = $this->gitRepository->getModifiedFilesWithStatus($revRange);
        $countOfAffectedFiles = count($affectedFiles);
        if ($countOfAffectedFiles != $count) {
            $adverb = $countOfAffectedFiles < $count ? "less" : "more";
            PHPUnit_Framework_Assert::fail("Commit affected $adverb files ($countOfAffectedFiles) then expected ($count)");
        }
    }

    /**
     * Converts $whichCommit (int) to a Git rev range
     *
     * @param int $whichCommit
     * @return string
     */
    private function getRevRange($whichCommit) {

        // We use 'git log' to get the commit info and construct the rev range this way:
        //
        //   "HEAD^..HEAD"      -> get the most recent commit
        //   "HEAD^^..HEAD^"    -> get second commit
        //   "HEAD^^^..HEAD^^"  -> get third commit
        //
        // So we just need to emit the currect number of carets (works fine for small number of commits)

        $numCarets = abs($whichCommit);
        $startRevisionCarets = str_repeat("^", $numCarets + 1);
        $endRevisionCarets = str_repeat("^", $numCarets);

        $revRange = "HEAD$startRevisionCarets..HEAD$endRevisionCarets";
        return $revRange;
    }

    public function assertCleanWorkingDirectory() {
        $gitStatus = $this->gitRepository->getStatus();
        if (!empty($gitStatus)) {
            PHPUnit_Framework_Assert::fail("Expected clean working directory but got:\n$gitStatus");
        }
    }

    /**
     * @param $path
     * @param $whichCommit
     * @return mixed
     * @throws Exception
     */
    private function expandPath($path, $whichCommit) {
        if (Strings::contains($path, "%vpdb%")) {
            $path = str_ireplace("%vpdb%", "wp-content/vpdb", $path);
        }
        if (Strings::contains($path, "%VPID%")) {
            $path = str_ireplace("%VPID%", ChangeInfoUtils::getVpid($this->getChangeInfo($this->getCommit($whichCommit))), $path);
            return $path;
        }
        return $path;
    }

}
