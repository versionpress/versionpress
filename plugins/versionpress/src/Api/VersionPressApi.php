<?php

namespace VersionPress\Api;

use VersionPress\ChangeInfos\ChangeInfoMatcher;
use VersionPress\DI\VersionPressServices;
use VersionPress\Git\GitLogPaginator;
use VersionPress\Git\GitRepository;
use VersionPress\Git\Reverter;
use VersionPress\Git\RevertStatus;
use VersionPress\Utils\BugReporter;

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
            array(array($this, 'getCommits'), \WP_JSON_Server::READABLE),
        );
        $routes[$this->base . '/undo'] = array(
            array(array($this, 'undoCommit'), \WP_JSON_Server::READABLE),
        );
        $routes[$this->base . '/rollback'] = array(
            array(array($this, 'rollbackToCommit'), \WP_JSON_Server::READABLE),
        );
        $routes[$this->base . '/can-revert'] = array(
            array(array($this, 'canRevert'), \WP_JSON_Server::READABLE),
        );
        $routes[$this->base . '/submit-bug'] = array(
            array(array($this, 'submitBug'), \WP_JSON_Server::CREATABLE | \WP_JSON_Server::ACCEPT_JSON),
        );
        return $routes;
    }

    /**
     * @param int $page
     * @return array|\WP_Error
     */
    public function getCommits($page = 0) {
        global $versionPressContainer;
        /** @var GitRepository $repository */
        $repository = $versionPressContainer->resolve(VersionPressServices::REPOSITORY);
        $gitLogPaginator = new GitLogPaginator($repository);
        $gitLogPaginator->setCommitsPerPage(25);

        $page = intval($page);
        $commits = $gitLogPaginator->getPage($page);

        if(empty($commits)) {
            return new \WP_Error('notice', 'No more commits to show.', array('status' => 403));
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
            $isEnabled = $canUndoCommit || $canRollbackToThisCommit || $commit->getHash() === $initialCommitHash;

            $result[] = array(
                "hash" => $commit->getHash(),
                "date" => $commit->getDate()->format('c'),
                "message" => $changeInfo->getChangeDescription(),
                "canUndo" => $canUndoCommit,
                "canRollback" => $canRollbackToThisCommit,
                "isEnabled" => $isEnabled
            );
            $isFirstCommit = false;
        }
        return array(
            'pages' => $gitLogPaginator->getPrettySteps($page),
            'commits' => $result
        );
    }

    /**
     * @param string $commit
     * @return boolean|\WP_Error
     */
    public function undoCommit($commit) {
        return $this->revertCommit('undo', $commit);
    }

    /**
     * @param string $commit
     * @return boolean|\WP_Error
     */
    public function rollbackToCommit($commit) {
        return $this->revertCommit('rollback', $commit);
    }

    /**
     * @return boolean|\WP_Error
     */
    public function canRevert() {
        global $versionPressContainer;
        /** @var GitRepository $repository */
        $repository = $versionPressContainer->resolve(VersionPressServices::REPOSITORY);
        /** @var Reverter $reverter */
        $reverter = $versionPressContainer->resolve(VersionPressServices::REVERTER);

        return $reverter->canRevert();
    }

    /**
     * @param string $reverterMethod
     * @param string $commit
     * @return boolean|\WP_Error
     */
    public function revertCommit($reverterMethod, $commit) {
        global $versionPressContainer;
        /** @var GitRepository $repository */
        $repository = $versionPressContainer->resolve(VersionPressServices::REPOSITORY);
        /** @var Reverter $reverter */
        $reverter = $versionPressContainer->resolve(VersionPressServices::REVERTER);

        vp_enable_maintenance();
        $revertStatus = call_user_func(array($reverter, $reverterMethod), $commit);
        vp_disable_maintenance();

        if ($revertStatus !== RevertStatus::OK) {
            return $this->getError($revertStatus);
        }
        return true;
    }

    /**
     * @param string[] $data
     * @return boolean|\WP_Error
     */
    public function submitBug($data) {
        $email = $data['email'];
        $description = $data['description'];

        $bugReporter = new BugReporter('http://versionpress.net/report-problem');
        $reportedSuccessfully = $bugReporter->reportBug($email, $description);

        if ($reportedSuccessfully) {
            return true;
        } else {
            return new \WP_Error(
                'error',
                'There was a problem with sending bug report. Please try it again. Thank you.',
                array('status' => 403)
            );
        }
    }

    /**
     * @param string $status
     * @return \WP_Error
     */
    public function getError($status) {
        $errors = array(
            RevertStatus::MERGE_CONFLICT => array(
                'class' => 'error',
                'message' => 'Error: Overwritten changes can not be reverted.',
                'status' => 403
            ),
            RevertStatus::NOTHING_TO_COMMIT => array(
                'class' => 'updated',
                'message' => 'There was nothing to commit. Current state is the same as the one you want rollback to.',
                'status' => 200
            ),
            RevertStatus::VIOLATED_REFERENTIAL_INTEGRITY => array(
                'class' => 'error',
                'message' => 'Error: Objects with missing references cannot be restored. For example we cannot restore comment where the related post was deleted.',
                'status' => 403
            ),
        );

        $error = $errors[$status];
        return new \WP_Error(
            $error['class'],
            $error['message'],
            array('status' => $error['status'])
        );
    }
}
