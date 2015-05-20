<?php

namespace VersionPress\Api;

use VersionPress\ChangeInfos\ChangeInfoMatcher;
use VersionPress\DI\VersionPressServices;
use VersionPress\Git\GitLogPaginator;
use VersionPress\Git\GitRepository;

class VersionPressApi {

    /** @var \WP_JSON_ResponseHandler */
    protected $server;

    /** @var string */
    protected $base = '/versionpress';

    /** @var string */
    protected $type = 'versionpress';

    /**
     * @param \WP_JSON_ResponseHandler $server Server object
     */
    function __construct(\WP_JSON_ResponseHandler $server) {
        $this->server = $server;
    }

    /**
     * Register the VersionPress related routes
     *
     * @param array $routes Existing routes
     * @return array Modified routes
     */
    public function register_routes($routes = array()) {
        $routes[$this->base . '/commits'] = array(
            array( array( $this, 'getCommits' ), \WP_JSON_Server::READABLE ),
        );
        $routes[$this->base . '/undo'] = array(
            array( array( $this, 'undoCommits' ), \WP_JSON_Server::READABLE ),
        );
        $routes[$this->base . '/rollback'] = array(
            array( array( $this, 'rollbackToCommit' ), \WP_JSON_Server::READABLE ),
        );
        return $routes;
    }

    /**
     * @param int $page
     * @return array|false
     */
    public function getCommits($page = 0) {
        global $versionPressContainer;
        /** @var GitRepository $repository */
        $repository = $versionPressContainer->resolve(VersionPressServices::REPOSITORY);
        $gitLogPaginator = new GitLogPaginator($repository);
        $gitLogPaginator->setCommitsPerPage(25);
        $commits = $gitLogPaginator->getPage($page);

        if(empty($commits)) {
            return false;
        }

        $preActivationHash = trim(file_get_contents(VERSIONPRESS_ACTIVATION_FILE));
        if (empty($preActivationHash)) {
            $initialCommitHash = $repository->getInitialCommit()->getHash();
        } else {
            $initialCommitHash = $repository->getChildCommit($preActivationHash);
        }

        $canUndoCommit = $repository->wasCreatedAfter($commits[0]->getHash(), $initialCommitHash);
        $isFirstCommit = $page === 0;

        $result = array();
        foreach($commits as $commit) {
            $canUndoCommit = $canUndoCommit && ($commit->getHash() !== $initialCommitHash);
            $canRollbackToThisCommit = !$isFirstCommit && ($canUndoCommit || $commit->getHash() === $initialCommitHash);
            $changeInfo = ChangeInfoMatcher::buildChangeInfo($commit->getMessage());

            $result[] = array(
                "hash" => $commit->getHash(),
                "date" => $commit->getDate()->format('d-M-y H:i:s'),
                "message" => $changeInfo->getChangeDescription(),
                "canUndo" => $canUndoCommit,
                "canRollback" => $canRollbackToThisCommit
            );
            $isFirstCommit = false;
        }
        return $result;
    }

    /**
     * @param array[string] $commits
     * @return boolean
     */
    public function undoCommits($commits = array()) {
        return false;
    }

    /**
     * @param string $commit
     * @return boolean
     */
    public function rollbackToCommit($commit) {
        return false;
    }
}
