<?php
use VersionPress\ChangeInfos\CommitMessageParser;
use VersionPress\DI\VersionPressServices;
use VersionPress\Git\GitLogPaginator;
use VersionPress\Git\GitRepository;
use VersionPress\Git\RevertStatus;
use VersionPress\Initialization\VersionPressOptions;

if (isset($_GET['error'])) {
    $errors = [
        RevertStatus::MERGE_CONFLICT => [
            'class' => 'error',
            'message' => 'Error: Overwritten changes can not be reverted.'
        ],
        RevertStatus::NOTHING_TO_COMMIT => [
            'class' => 'updated',
            'message' => 'There was nothing to commit. Current state is the same as the one you want rollback to.'
        ],
        RevertStatus::VIOLATED_REFERENTIAL_INTEGRITY => [
            'class' => 'error',
            'message' => 'Error: Objects with missing references cannot be restored.
            For example we cannot restore comment where the related post was deleted.'
        ],
        RevertStatus::REVERTING_MERGE_COMMIT => [
            'class' => 'error',
            'message' => 'Error: It is not possible to undo merge commit.'
        ],
    ];

    $error = isset($errors[ $_GET['error']]) ? $errors[$_GET['error']] : null;
}

if (!empty($error)) {
    echo "<div class='$error[class]'><p>$error[message]</p></div>";
}
?>

<button id="vp-service-panel-button"><span class="icon icon-cog"></span></button>
<h2 id="vp-page-header">VersionPress</h2>

<div id="vp-service-panel" class="ServicePanel welcome-panel">
    <p class='warning'>
        Currently, VersionPress is in an <a href="http://docs.versionpress.net/en/getting-started/about-eap">
            <strong>Early Access phase</strong></a>.<br />
        Please understand that EAP releases are early versions of the software and as such might not fully support
        certain workflows, 3rd party plugins, hosts etc.
    </p>

    <h3>Community and support</h3>
    <ul>
        <li>
            Having trouble using VersionPress?
            Our <a href="http://docs.versionpress.net">documentation</a> has you covered.
        </li>
        <li>
            Can’t find what you need?
            Please visit our <a href="https://github.com/versionpress/support">support&nbsp;repository</a>.
        </li>
        <li>
            <a href="<?php echo admin_url('/admin.php?page=versionpress/admin/system-info.php');?>">
                System information
            </a> page.
        </li>
    </ul>
</div>

<?php
$showWelcomePanel = get_user_meta(get_current_user_id(), VersionPressOptions::USER_META_SHOW_WELCOME_PANEL, true);

if ($showWelcomePanel === "") {
?>

    <div id="welcome-panel" class="welcome-panel">

        <a id="vp-welcome-panel-close-button" class="welcome-panel-close" href="">Dismiss</a>

        <div class="welcome-panel-content">

            <h3>Welcome!</h3>

            <p class="about-description">
                Below is the main VersionPress table which will grow as changes are made to this site.
                You can <strong>Undo</strong> specific changes from the history or <strong>Roll back</strong>
                the site entirely to a previous state.
            </p>
        </div>

    </div>

<?php } ?>

<table id="versionpress-commits-table" class="wp-list-table widefat fixed posts">
    <tr>
        <th class="manage-column column-date">Date</th>
        <th class="manage-column column-message">Message</th>
        <th class="manage-column column-actions"></th>
    </tr>
    <tbody id="the-list">
    <?php
    global $versionPressContainer;
    /** @var GitRepository $repository */
    $repository = $versionPressContainer->resolve(VersionPressServices::GIT_REPOSITORY);
    /** @var CommitMessageParser $commitMessageParser */
    $commitMessageParser = $versionPressContainer->resolve(VersionPressServices::COMMIT_MESSAGE_PARSER);

    $preActivationHash = trim(file_get_contents(VERSIONPRESS_ACTIVATION_FILE));
    if (empty($preActivationHash)) {
        $initialCommitHash = $repository->getInitialCommit()->getHash();
    } else {
        $initialCommitHash = $repository->getChildCommit($preActivationHash);
    }

    $gitLogPaginator = new GitLogPaginator($repository);
    $gitLogPaginator->setCommitsPerPage(25);
    $page = isset($_GET['vp-page']) ? intval($_GET['vp-page']) : 0;
    $commits = $gitLogPaginator->getPage($page);

    $isChildOfInitialCommit = $repository->wasCreatedAfter($commits[0]->getHash(), $initialCommitHash);
    $isFirstCommit = $page === 0;

    $disabledCommitsMessage = "
        <tr class=\"post-1 type-post status-publish format-standard hentry
                    category-uncategorized alternate note level-0\">
            <td colspan=\"3\">VersionPress is not able to undo changes made before it has been activated.</td>
        </tr>
        ";

    if (!$isChildOfInitialCommit && $commits[0]->getHash() !== $initialCommitHash) {
        echo $disabledCommitsMessage;
    }

    foreach ($commits as $key => $commit) {
        $isChildOfInitialCommit = $isChildOfInitialCommit && ($commit->getHash() !== $initialCommitHash);
        $canUndoCommit = $isChildOfInitialCommit && !$commit->isMerge();
        $canRollbackToThisCommit = !$isFirstCommit
            && ($isChildOfInitialCommit || $commit->getHash() === $initialCommitHash);
        $commitDate = $commit->getDate()->format('d-M-y H:i:s');

        $changeInfo = $commitMessageParser->parse($commit->getMessage());
        $undoSnippet = "<a " .
            "href='" .
                admin_url('admin.php?action=vp_show_undo_confirm&method=undo&commit=' . $commit->getHash()) . "' " .
            "class='vp-undo' " .
            "data-commit='" . $commit->getHash() . "' " .
            "data-commit-message=\"" . htmlspecialchars($changeInfo->getChangeDescription()) . "\"" .
            "style='text-decoration:none; white-space:nowrap;' " .
            "title='Reverts changes done by this commit'>Undo this</a>";

        $rollbackSnippet = "<a " .
            "href='" .
                admin_url('admin.php?action=vp_show_undo_confirm&method=rollback&commit=' . $commit->getHash()) . "' " .
            "class='vp-rollback' " .
            "data-commit='" . $commit->getHash() . "' " .
            "data-commit-date='" . $commitDate . "'" .
            "style='text-decoration:none; white-space:nowrap;' " .
            "title='Reverts site back to this state; effectively undos all the change up to this commit'>" .
            "Roll back to this</a>";

        $versioningSnippet = "";
        if ($canUndoCommit) {
            $versioningSnippet .= $undoSnippet;
        }
        if ($canUndoCommit && $canRollbackToThisCommit) {
            $versioningSnippet .= "&nbsp;|&nbsp;";
        }
        if ($canRollbackToThisCommit) {
            $versioningSnippet .= $rollbackSnippet;
        }

        $isEnabled = $isChildOfInitialCommit || $canRollbackToThisCommit || $commit->getHash() === $initialCommitHash;

        $message = $changeInfo->getChangeDescription();
        echo "
        <tr class=\"post-1 type-post status-publish format-standard hentry category-uncategorized
                    alternate level-0" . ($isEnabled ? "" : " disabled") . "\">
            <td title=\"{$commitDate}\">{$commit->getRelativeDate()}</td>
            <td>$message</td>
            <td style=\"text-align: right\">
                $versioningSnippet
            </td>
        </tr>";

        if ($commit->getHash() === $initialCommitHash && $key < count($commits) - 1) {
            echo $disabledCommitsMessage;
        }

        $isFirstCommit = false;
    }
    ?>
    </tbody>
    <tfoot>
    <tr>
        <td colspan="3">
            <?php
            $pageNumbers = $gitLogPaginator->getPrettySteps($page);

            $i = 0;
            $links = "";
            $lastNumber = 0;

            foreach ($pageNumbers as $pageNumber) {
                $divider = "";
                if ($i > 0 && $lastNumber != $pageNumber-1) {
                    $divider = "&hellip;";
                } elseif ($i > 0) {
                    $divider = "|";
                }

                $links .= " " . $divider . " ";
                $pageUrl = add_query_arg('vp-page', $pageNumber, menu_page_url('versionpress', false));
                if ($pageNumber == $page) {
                    $links .= $pageNumber + 1;
                } else {
                    $links .= "<a href=\"$pageUrl\">" . ($pageNumber + 1) . "</a>";
                }

                $lastNumber = $pageNumber;
                $i += 1;
            }

            echo $links;
            ?>
        </td>
    </tr>
    </tfoot>
</table>
