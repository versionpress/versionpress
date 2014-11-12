<?php

/**
 * Useful for pagination of git log. Uses the `GitStatic` class.
 */
class GitLogPaginator {

    /**
     * @var GitRepository
     */
    private $repository;
    private $commitsPerPage = 25;
    private $isLastPage = false;
    private $numberOfCommits;

    function __construct(GitRepository $repository) {
        $this->repository = $repository;
    }


    /**
     * Returns a subset of commits ordered from newest to oldest.
     *
     * @param $pageNumber
     * @return Commit[]
     */
    public function getPage($pageNumber) {
        $this->numberOfCommits = $this->repository->getNumberOfCommits();

        $firstCommitIndex = $pageNumber * $this->commitsPerPage;
        $lastCommitIndex = ($pageNumber + 1) * $this->commitsPerPage;

        if($lastCommitIndex >= $this->numberOfCommits) {
            $range = sprintf("HEAD~%s", $firstCommitIndex);
            $this->isLastPage = true;
        } else {
            $range = sprintf("HEAD~%s..HEAD~%s", $lastCommitIndex, $firstCommitIndex);
            $this->isLastPage = false;
        }

        return $this->repository->log($range);
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
     * @param int $commitsPerPage
     */
    public function setCommitsPerPage($commitsPerPage) {
        $this->commitsPerPage = $commitsPerPage;
    }

    /**
     * Returns a subset of pages useful for pagination.
     * For example if there are 1000 commits, 25 per page and you are on page 10,
     * it returns 0,7,8,9,10,11,12,13,20,29,39.
     * The algorithm is from VisualPaginator component for Nette Framework.
     *
     * @param $currentPage
     * @return array
     */
    public function getPrettySteps($currentPage) {
        $page = $currentPage;
        $pageCount = ceil($this->numberOfCommits / (double)$this->commitsPerPage);

        if ($pageCount < 2) {
            return array();
        }

        $arr = range(max(0, $page - 3), min($pageCount - 1, $page + 3));
        $count = 4;
        $quotient = ($pageCount - 1) / $count;
        for ($i = 0; $i <= $count; $i++) {
            $arr[] = round($quotient * $i);
        }
        sort($arr);
        $steps = array_values(array_unique($arr));

        return $steps;
    }
}