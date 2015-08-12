<?php

namespace VersionPress\Api;

require_once ABSPATH . 'wp-admin/includes/file.php';

use VersionPress\ChangeInfos\ChangeInfoMatcher;
use VersionPress\DI\VersionPressServices;
use VersionPress\Git\GitLogPaginator;
use VersionPress\Git\GitRepository;
use VersionPress\Git\Reverter;
use VersionPress\Git\RevertStatus;
use VersionPress\Initialization\VersionPressOptions;
use VersionPress\Utils\BugReporter;
use VersionPress\Api\BundledWpApi\WP_REST_Server;
use VersionPress\Api\BundledWpApi\WP_REST_Request;
use VersionPress\Api\BundledWpApi\WP_REST_Response;

class VersionPressApi {

    /**
     * Register the VersionPress related routes
     */
    public function register_routes() {
        $namespace = 'versionpress';

        register_vp_rest_route($namespace, '/commits', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'getCommits'),
            'args' => array(
                'page' => array(
                    'default' => '0'
                )
            )
        ));

        register_vp_rest_route($namespace, '/undo', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'undoCommit'),
            'args' => array(
                'commit' => array(
                    'required' => true
                )
            )
        ));

        register_vp_rest_route($namespace, '/rollback', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'rollbackToCommit'),
            'args' => array(
                'commit' => array(
                    'required' => true
                )
            )
        ));

        register_vp_rest_route($namespace, '/can-revert', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'canRevert')
        ));

        register_vp_rest_route($namespace, '/submit-bug', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'submitBug'),
            'args' => array(
                'email' => array(
                    'required' => true
                ),
                'description' => array(
                    'required' => true
                )
            )
        ));

        register_vp_rest_route($namespace, '/display-welcome-panel', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'displayWelcomePanel')
        ));

        register_vp_rest_route($namespace, '/hide-welcome-panel', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'hideWelcomePanel')
        ));
    }

    /**
     * @param WP_REST_Request $request
     * @return WP_REST_Response|\WP_Error
     */
    public function getCommits(WP_REST_Request $request) {
        global $versionPressContainer;
        /** @var GitRepository $repository */
        $repository = $versionPressContainer->resolve(VersionPressServices::REPOSITORY);
        $gitLogPaginator = new GitLogPaginator($repository);
        $gitLogPaginator->setCommitsPerPage(25);

        $page = intval($request['page']);
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
        return new WP_REST_Response(array(
            'pages' => $gitLogPaginator->getPrettySteps($page),
            'commits' => $result
        ));
    }

    /**
     * @param WP_REST_Request $request
     * @return WP_REST_Response|\WP_Error
     */
    public function undoCommit(WP_REST_Request $request) {
        return $this->revertCommit('undo', $request['commit']);
    }

    /**
     * @param WP_REST_Request $request
     * @return WP_REST_Response|\WP_Error
     */
    public function rollbackToCommit(WP_REST_Request $request) {
        return $this->revertCommit('rollback', $request['commit']);
    }

    /**
     * @return WP_REST_Response|\WP_Error
     */
    public function canRevert() {
        global $versionPressContainer;
        /** @var GitRepository $repository */
        $repository = $versionPressContainer->resolve(VersionPressServices::REPOSITORY);
        /** @var Reverter $reverter */
        $reverter = $versionPressContainer->resolve(VersionPressServices::REVERTER);

        return new WP_REST_Response($reverter->canRevert());
    }

    /**
     * @param string $reverterMethod
     * @param string $commit
     * @return WP_REST_Response|\WP_Error
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
        return new WP_REST_Response(true);
    }

    /**
     * @param WP_REST_Request $request
     * @return WP_REST_Response|\WP_Error
     */
    public function submitBug(WP_REST_Request $request) {
        $email = $request['email'];
        $description = $request['description'];

        $bugReporter = new BugReporter('http://versionpress.net/report-problem');
        $reportedSuccessfully = $bugReporter->reportBug($email, $description);

        if ($reportedSuccessfully) {
            return new WP_REST_Response(true);
        } else {
            return new \WP_Error(
                'error',
                'There was a problem with sending bug report. Please try it again. Thank you.',
                array('status' => 403)
            );
        }
    }

    /**
     * @return WP_REST_Response
     */
    public function displayWelcomePanel() {
        $showWelcomePanel = get_user_meta(get_current_user_id(), VersionPressOptions::USER_META_SHOW_WELCOME_PANEL, true);
        return new WP_REST_Response($showWelcomePanel === "");
    }

    /**
     * @return WP_REST_Response
     */
    public function hideWelcomePanel() {
        update_user_meta(get_current_user_id(), VersionPressOptions::USER_META_SHOW_WELCOME_PANEL, "0");
        return new WP_REST_Response(null, 204);
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
