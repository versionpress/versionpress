<?php

namespace VersionPress\Api;

require_once ABSPATH . 'wp-admin/includes/file.php';

use Nette\Utils\Strings;
use VersionPress\Actions\ActionsInfoProvider;
use VersionPress\ChangeInfos\ChangeInfoEnvelope;
use VersionPress\ChangeInfos\ChangeInfoFactory;
use VersionPress\ChangeInfos\CommitMessageParser;
use VersionPress\ChangeInfos\EntityChangeInfo;
use VersionPress\ChangeInfos\TrackedChangeInfo;
use VersionPress\ChangeInfos\UntrackedChangeInfo;
use VersionPress\DI\VersionPressServices;
use VersionPress\Git\Commit;
use VersionPress\Git\CommitMessage;
use VersionPress\Git\GitLogPaginator;
use VersionPress\Git\GitRepository;
use VersionPress\Git\Reverter;
use VersionPress\Git\RevertStatus;
use VersionPress\Initialization\VersionPressOptions;
use VersionPress\Synchronizers\SynchronizationProcess;
use VersionPress\Utils\ArrayUtils;
use VersionPress\Utils\AutocompleteUtils;
use VersionPress\Utils\QueryLanguageUtils;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class VersionPressApi
{

    /** @var GitRepository */
    private $gitRepository;
    /** @var Reverter */
    private $reverter;
    /** @var SynchronizationProcess */
    private $synchronizationProcess;

    const NAMESPACE_VP = 'versionpress';

    /** @var CommitMessageParser */
    private $commitMessageParser;

    public function __construct(GitRepository $gitRepository, Reverter $reverter, SynchronizationProcess $synchronizationProcess, CommitMessageParser $commitMessageParser)
    {
        $this->gitRepository = $gitRepository;
        $this->reverter = $reverter;
        $this->synchronizationProcess = $synchronizationProcess;
        $this->commitMessageParser = $commitMessageParser;
    }

    /**
     * Register the VersionPress related routes
     */
    public function registerRoutes()
    {
        $this->registerRestRoute('/commits', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'getCommits'],
            'args' => [
                'page' => [
                    'default' => '0'
                ],
                'query' => [
                    'default' => []
                ]
            ]
        ]);

        $this->registerRestRoute('/undo', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'undoCommit'),
            'args' => [
                'commits' => [
                    'required' => true
                ]
            ]
        ]);

        $this->registerRestRoute('/rollback', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'rollbackToCommit'),
            'args' => [
                'commit' => [
                    'required' => true
                ]
            ]
        ]);

        $this->registerRestRoute('/can-revert', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'canRevert']
        ]);

        $this->registerRestRoute('/diff', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'getDiff'],
            'args' => [
                'commit' => [
                    'default' => null
                ]
            ]
        ]);

        $this->registerRestRoute('/display-welcome-panel', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'displayWelcomePanel']
        ]);

        $this->registerRestRoute('/hide-welcome-panel', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'hideWelcomePanel')
        ]);

        $this->registerRestRoute('/should-update', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'shouldUpdate']
        ]);

        $this->registerRestRoute('/git-status', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'getGitStatus']
        ]);

        $this->registerRestRoute('/commit', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'commit'),
            'args' => [
                'commit-message' => [
                    'required' => true
                ]
            ]
        ]);

        $this->registerRestRoute('/discard-changes', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'discardChanges')
        ]);

        $this->registerRestRoute('/autocomplete-config', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'getAutocompleteConfig')
        ]);
    }

    private function registerRestRoute($route, $args = [], $override = false)
    {
        $args['callback'] = $this->handleErrorOutput($args['callback']);
        if (!isset($args['permission_callback'])) {
            $args['permission_callback'] = [$this, 'checkPermissions'];
        }
        return register_rest_route(self::NAMESPACE_VP, $route, $args, $override);
    }

    /**
     * Prevents unexpected output from displaying to output, adds it to the response json instead.
     *
     * @param callable|string $routeHandler
     * @return \Closure
     */
    private function handleErrorOutput($routeHandler)
    {
        return function (WP_REST_Request $request) use ($routeHandler) {
            ob_start();
            /** @var WP_REST_Response|WP_Error $response */
            $response = is_callable($routeHandler)
                ? $routeHandler($request)
                : $this->$routeHandler($request);

            $data = ($response instanceof WP_Error)
                ? $response->get_error_data()
                : $response->get_data();

            $responseArr = [
                '__VP__' => true,
                'data' => $data
            ];

            if (ob_get_length() > 0) {
                $bufferContents = ob_get_clean();
                $data['phpBuffer'] = $bufferContents;
            }

            if ($response instanceof WP_Error) {
                $response->add_data($responseArr);
            } else {
                $response->set_data($responseArr);
            }

            return $response;
        };
    }

    /**
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function getCommits(WP_REST_Request $request)
    {
        $gitLogPaginator = new GitLogPaginator($this->gitRepository);

        $query = urldecode(stripslashes($request['query']));
        $rules = QueryLanguageUtils::createRulesFromQueries([$query]);
        $gitLogQuery = !empty($rules)
            ? QueryLanguageUtils::createGitLogQueryFromRule($rules[0])
            : '';
        $gitLogPaginator->setQuery($gitLogQuery);
        $gitLogPaginator->setCommitsPerPage(25);

        $page = intval($request['page']);
        $commits = $gitLogPaginator->getPage($page);

        if (empty($commits)) {
            return new WP_Error('notice', 'No more commits to show.', ['status' => 404]);
        }

        $initialCommitHash = $this->getInitialCommitHash();

        $isChildOfInitialCommit = $this->gitRepository->wasCreatedAfter($commits[0]->getHash(), $initialCommitHash);
        $isFirstCommit = $page === 0;

        $result = [];
        foreach ($commits as $commit) {
            $isChildOfInitialCommit = $isChildOfInitialCommit && $this->gitRepository->wasCreatedAfter($commit->getHash(), $initialCommitHash);
            $canUndoCommit = $isChildOfInitialCommit && !$commit->isMerge();
            $canRollbackToThisCommit = !$isFirstCommit &&
                ($isChildOfInitialCommit || $commit->getHash() === $initialCommitHash);

            $changeInfo = $this->commitMessageParser->parse($commit->getMessage());
            $isEnabled = $isChildOfInitialCommit || $commit->getHash() === $initialCommitHash;

            $skipVpdbFiles = $changeInfo->getChangeInfoList()[0] instanceof TrackedChangeInfo;
            $fileChanges = $this->getFileChanges($commit, $skipVpdbFiles);

            $environment = $changeInfo instanceof ChangeInfoEnvelope ? $changeInfo->getEnvironment() : '?';
            $changeInfoList = $changeInfo instanceof ChangeInfoEnvelope ? $changeInfo->getChangeInfoList() : [];

            $result[] = [
                "hash" => $commit->getHash(),
                "date" => $commit->getDate()->format('c'),
                "message" => $changeInfo->getChangeDescription(),
                "parentHashes" => $commit->getParentHashes(),
                "canUndo" => $canUndoCommit,
                "canRollback" => $canRollbackToThisCommit,
                "isEnabled" => $isEnabled,
                "isInitial" => $commit->getHash() === $initialCommitHash,
                "isMerge" => $commit->isMerge(),
                "environment" => $environment,
                "changes" => array_values(array_filter(array_merge(
                    $this->convertChangeInfoList($changeInfoList),
                    $fileChanges
                ))),
                "author" => [
                    "name" => $commit->getAuthorName(),
                    "email" => $commit->getAuthorEmail(),
                    "avatar" => get_avatar_url($commit->getAuthorEmail())
                ]
            ];
            $isFirstCommit = false;
        }
        return new WP_REST_Response([
            'pages' => $gitLogPaginator->getPrettySteps($page),
            'commits' => $result
        ]);
    }

    /**
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function undoCommits(WP_REST_Request $request)
    {
        $commitHashes = explode(',', $request['commits']);

        $initialCommitHash = $this->getInitialCommitHash();

        foreach ($commitHashes as $commitHash) {
            $log = $this->gitRepository->log($commitHash);
            if (!preg_match('/^[0-9a-f]+$/', $commitHash) || count($log) === 0) {
                return new WP_Error('error', 'Invalid commit hash', ['status' => 404]);
            }
            if ($log[0]->isMerge()) {
                return new WP_Error('error', 'Cannot undo merge commit', ['status' => 403]);
            }
            if (!$this->gitRepository->wasCreatedAfter($commitHash, $initialCommitHash)) {
                return new WP_Error('error', 'Cannot undo changes before initial commit', ['status' => 403]);
            }
        }

        return $this->revertCommits('undo', $commitHashes);
    }

    /**
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function rollbackToCommit(WP_REST_Request $request)
    {
        $commitHash = $request['commit'];

        $initialCommitHash = $this->getInitialCommitHash();

        $log = $this->gitRepository->log($commitHash);
        if (!preg_match('/^[0-9a-f]+$/', $commitHash) || count($log) === 0) {
            return new WP_Error('error', 'Invalid commit hash', ['status' => 404]);
        }
        if (!$this->gitRepository->wasCreatedAfter($commitHash, $initialCommitHash) &&
            $log[0]->getHash() !== $initialCommitHash
        ) {
            return new WP_Error('error', 'Cannot roll back before initial commit', ['status' => 403]);
        }
        if ($log[0]->getHash() === $this->gitRepository->getLastCommitHash()) {
            return new WP_Error(
                'error',
                'Nothing to commit. Current state is the same as the one you want rollback to.',
                ['status' => 403]
            );
        }

        return $this->revertCommits('rollback', [$commitHash]);
    }

    /**
     * @return WP_REST_Response|WP_Error
     */
    public function canRevert()
    {
        return new WP_REST_Response($this->reverter->canRevert());
    }

    /**
     * @param string $reverterMethod
     * @param array $commits
     * @return WP_REST_Response|WP_Error
     */
    public function revertCommits($reverterMethod, $commits)
    {
        vp_enable_maintenance();
        $revertStatus = call_user_func([$this->reverter, $reverterMethod], $commits);
        vp_disable_maintenance();

        if ($revertStatus !== RevertStatus::OK) {
            return $this->getError($revertStatus);
        }
        return new WP_REST_Response(true);
    }

    /**
     * Returns diff of given commit.
     * If there's provided no commit hash, returns diff of working directory and HEAD.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function getDiff(WP_REST_Request $request)
    {
        $commitHash = $request['commit'];

        if (!preg_match('/^[0-9a-f]*$/', $commitHash)) {
            return new WP_Error(
                'error',
                'Invalid commit hash',
                ['status' => 404]
            );
        }

        $diff = $this->gitRepository->getDiff($commitHash);

        if (strlen($diff) > 50 * 1024) { // 50 kB is maximum size for diff (see WP-49)
            return new WP_Error(
                'error',
                'The diff is too large to show here. Please use some Git client. Thank you.',
                ['status' => 403]
            );
        }

        return new WP_REST_Response(['diff' => $diff]);
    }

    /**
     * @return WP_REST_Response
     */
    public function displayWelcomePanel()
    {
        $showWelcomePanel = get_user_meta(
            get_current_user_id(),
            VersionPressOptions::USER_META_SHOW_WELCOME_PANEL,
            true
        );
        return new WP_REST_Response($showWelcomePanel === "");
    }

    /**
     * @return WP_REST_Response
     */
    public function hideWelcomePanel()
    {
        update_user_meta(get_current_user_id(), VersionPressOptions::USER_META_SHOW_WELCOME_PANEL, "0");
        return new WP_REST_Response(null, 204);
    }

    public function shouldUpdate(WP_REST_Request $request)
    {
        global $versionPressContainer;
        /** @var GitRepository $repository */
        $repository = $versionPressContainer->resolve(VersionPressServices::GIT_REPOSITORY);

        $latestCommit = $request['latestCommit'];

        $query = urldecode(stripslashes($request['query']));
        $rules = QueryLanguageUtils::createRulesFromQueries([$query]);
        $gitLogQuery = !empty($rules)
            ? QueryLanguageUtils::createGitLogQueryFromRule($rules[0])
            : '';
        $repoLatestCommit = $repository->getLastCommitHash($gitLogQuery);

        return new WP_REST_Response([
            "update" => $repository->wasCreatedAfter($repoLatestCommit, $latestCommit),
            "cleanWorkingDirectory" => $repository->isCleanWorkingDirectory()
        ]);
    }

    /**
     * Returns list of files with modification types.
     *
     * Example:
     * [
     *  ["A", "some-file.txt"],
     *  ["M", "other-file.txt"]
     * ]
     *
     * Modification types:
     * ?? - untracked file
     * A - added file
     * M - modified file
     * D - deleted file
     *
     * @return WP_REST_Response
     */
    public function getGitStatus()
    {
        global $versionPressContainer;
        /** @var GitRepository $repository */
        $repository = $versionPressContainer->resolve(VersionPressServices::GIT_REPOSITORY);

        return new WP_REST_Response($repository->getStatus(true));
    }

    /**
     * Creates manual commit. Adds everything to stage.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function commit(WP_REST_Request $request)
    {
        $currentUser = wp_get_current_user();
        if ($currentUser->ID === 0) {
            return new WP_Error(
                'error',
                'You don\'t have permission to do this.',
                ['status' => 403]
            );
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $authorName = $currentUser->display_name;
        /** @noinspection PhpUndefinedFieldInspection */
        $authorEmail = $currentUser->user_email;

        $this->gitRepository->stageAll();

        $status = $this->gitRepository->getStatus(true);
        if (ArrayUtils::any($status, function ($fileStatus) {
            $vpdbName = basename(VP_VPDB_DIR);
            return Strings::contains($fileStatus[1], $vpdbName);
        })
        ) {
            $this->updateDatabase($status);
        }

        $commitMessage = new CommitMessage($request['commit-message']);
        $changeInfoEnvelope = new ChangeInfoEnvelope([new UntrackedChangeInfo($commitMessage)]);

        $this->gitRepository->commit($changeInfoEnvelope->getCommitMessage(), $authorName, $authorEmail);
        return new WP_REST_Response(true);
    }

    private function updateDatabase($status)
    {
        $fullDiff = $this->gitRepository->getDiff();

        $diffFiles = explode('diff --git', $fullDiff);

        $vpdbName = basename(VP_VPDB_DIR);
        $vpidRegex = "/([\\da-f]{32})/i";
        $optionRegex = "/.*{$vpdbName}[\\/\\\\]options[\\/\\\\].+[\\/\\\\](.+)\\.ini/i";

        $entitiesToSynchronize = [];

        foreach ($diffFiles as $diff) {
            $firstLine = substr($diff, 0, strpos($diff, "\n"));
            $parent = null;

            if (preg_match($optionRegex, $firstLine, $matches)) {
                $entitiesToSynchronize[] = ['vp_id' => $matches[1], 'parent' => null];
            } elseif (preg_match($vpidRegex, $firstLine, $matches)) {
                $parent = $matches[1];
            }

            preg_match_all($vpidRegex, $diff, $vpidMatches);

            foreach ($vpidMatches[1] as $match) {
                $entitiesToSynchronize[] = ['vp_id' => $match, 'parent' => $parent];
            }
        }

        $entitiesToSynchronize = array_unique($entitiesToSynchronize, SORT_REGULAR);
        $this->synchronizationProcess->synchronize($entitiesToSynchronize);
    }

    /**
     * Discards all changes in working directory.
     * @return WP_REST_Response
     */
    public function discardChanges()
    {
        global $versionPressContainer;
        /** @var GitRepository $repository */
        $repository = $versionPressContainer->resolve(VersionPressServices::GIT_REPOSITORY);

        $result = $repository->clearWorkingDirectory();

        return new WP_REST_Response($result);
    }

    /**
     * Returns current WP configuration for autocomplete component.
     * @return WP_REST_Response
     */
    public function getAutocompleteConfig()
    {
        global $versionPressContainer;
        /** @var ActionsInfoProvider $actionsInfoProvider */
        $actionsInfoProvider = $versionPressContainer->resolve(VersionPressServices::ACTIONSINFO_PROVIDER_ACTIVE_PLUGINS);

        $config = AutocompleteUtils::createAutocompleteConfig($actionsInfoProvider);

        return new WP_REST_Response($config);
    }

    /**
     * @param string $status
     * @return WP_Error
     */
    public function getError($status)
    {
        $errors = [
            RevertStatus::MERGE_CONFLICT => [
                'class' => 'error',
                'message' => 'Error: Overwritten changes can not be reverted.',
                'status' => 403
            ],
            RevertStatus::NOTHING_TO_COMMIT => [
                'class' => 'updated',
                'message' => 'There was nothing to commit. Current state is the same as the one you want rollback to.',
                'status' => 403
            ],
            RevertStatus::VIOLATED_REFERENTIAL_INTEGRITY => [
                'class' => 'error',
                'message' => 'Error: Objects with missing references cannot be restored. ' .
                    'For example we cannot restore comment where the related post was deleted.',
                'status' => 403
            ],
            RevertStatus::REVERTING_MERGE_COMMIT => [
                'class' => 'error',
                'message' => 'Error: It is not possible to undo merge commit.',
                'status' => 403
            ],
        ];

        $error = $errors[$status];
        return new WP_Error(
            $error['class'],
            $error['message'],
            ['status' => $error['status']]
        );
    }

    /**
     * @param WP_REST_Request $request
     * @return WP_Error|bool
     */
    public function checkPermissions(WP_REST_Request $request)
    {
        return !VERSIONPRESS_REQUIRE_API_AUTH || current_user_can('manage_options')
            ? true
            : new WP_Error(
                'error',
                'You don\'t have permission to do this.',
                ['status' => 403]
            );
    }

    private function convertChangeInfoList($getChangeInfoList)
    {
        return array_map([$this, 'convertChangeInfo'], $getChangeInfoList);
    }

    private function convertChangeInfo($changeInfo)
    {
        if ($changeInfo instanceof UntrackedChangeInfo) {
            return null;
        }

        /** @var TrackedChangeInfo $changeInfo */

        $change = [
            'type' => $changeInfo->getScope(),
            'action' => $changeInfo->getAction(),
            'tags' => $changeInfo->getCustomTags(),
        ];

        if ($changeInfo instanceof EntityChangeInfo) {
            $change['name'] = $changeInfo->getId();
        }

        if ($changeInfo->getScope() === 'plugin') {
            $pluginTags = $changeInfo->getCustomTags();
            $pluginName = $pluginTags['VP-Plugin-Name'];
            $change['name'] = $pluginName;
        }

        if ($changeInfo->getScope() === 'theme') {
            $themeTags = $changeInfo->getCustomTags();
            $themeName = $themeTags['VP-Theme-Name'];
            $change['name'] = $themeName;
        }

        if ($changeInfo->getScope() === 'wordpress') {
            $change['name'] = $changeInfo->getId();
        }

        if ($changeInfo->getScope() === 'versionpress' &&  ($changeInfo->getAction() === 'undo' || $changeInfo->getAction() === 'rollback')) {
            $commit = $this->gitRepository->getCommit($changeInfo->getId());
            $change['tags']['VP-Commit-Details'] = [
                'message' => $commit->getMessage()->getUnprefixedSubject(),
                'date' => $commit->getDate()->format(\DateTime::ISO8601)
            ];
        }

        return $change;
    }

    /**
     * @param Commit $commit
     * @param bool $skipVpdbFiles
     * @return array
     */
    private function getFileChanges(Commit $commit, $skipVpdbFiles)
    {
        $changedFiles = $commit->getChangedFiles();

        if ($skipVpdbFiles) {
            $changedFiles = array_filter($changedFiles, function ($changedFile) {
                $path = str_replace('\\', '/', realpath(VP_PROJECT_ROOT) . '/' . $changedFile['path']);
                $vpdbPath = str_replace('\\', '/', realpath(VP_VPDB_DIR));

                return !Strings::startsWith($path, $vpdbPath);
            });
        }

        $fileChanges = array_map(function ($changedFile) {
            $status = $changedFile['status'];
            $filename = $changedFile['path'];

            return [
                'type' => 'file',
                'action' => $status === 'A' ? 'add' : ($status === 'M' ? 'modify' : 'delete'),
                'name' => $filename,
            ];
        }, $changedFiles);

        return $fileChanges;
    }

    /**
     * @return string
     */
    private function getInitialCommitHash()
    {
        $preActivationHash = trim(file_get_contents(VERSIONPRESS_ACTIVATION_FILE));
        if (empty($preActivationHash)) {
            return $this->gitRepository->getInitialCommit()->getHash();
        }
        return $this->gitRepository->getChildCommit($preActivationHash);
    }
}
