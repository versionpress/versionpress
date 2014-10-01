<?php

/**
 * Useful for pagination of git log. Uses the `Git` class.
 */
class GitLogPaginator {

    private $defaultNumberOfCommits = 25;
    private $isLastPage = false;

    /**
     * Returns a subset of commits ordered from newest to oldest.
     *
     * @param $pageNumber
     * @param int $numberOfCommits
     * @return Commit[]
     */
    public function getPage($pageNumber, $numberOfCommits = 0) {
        if($numberOfCommits < 1) $numberOfCommits = $this->defaultNumberOfCommits;

        $maxCommitIndex = Git::getNumberOfCommits() - 1;

        $firstCommitIndex = $pageNumber * $numberOfCommits;
        $lastCommitIndex = ($pageNumber + 1) * $numberOfCommits;

        $range = sprintf("HEAD~%s..HEAD~%s", min($lastCommitIndex, $maxCommitIndex), $firstCommitIndex);

        $this->isLastPage = $lastCommitIndex == $maxCommitIndex;
        return Git::log($range);
    }

    /**
     * Returns true if the last loaded page was the last one.
     *
     * @return boolean
     */
    public function isLastPage() {
        return $this->isLastPage;
    }

    /**
     * @param int $defaultNumberOfCommits
     */
    public function setDefaultNumberOfCommits($defaultNumberOfCommits) {
        $this->defaultNumberOfCommits = $defaultNumberOfCommits;
    }
} 