<?php
use VersionPress\ChangeInfos\ChangeInfoMatcher;
use VersionPress\DI\VersionPressServices;
use VersionPress\Git\GitLogPaginator;
use VersionPress\Git\GitRepository;
use VersionPress\Git\RevertStatus;
use VersionPress\Initialization\VersionPressOptions;

if (isset($_GET['error'])) {
    $errors = array(
        RevertStatus::MERGE_CONFLICT => array(
            'class' => 'error',
            'message' => 'Error: Overwritten changes can not be reverted.'
        ),
        RevertStatus::NOTHING_TO_COMMIT => array(
            'class' => 'updated',
            'message' => 'There was nothing to commit. Current state is the same as the one you want rollback to.'
        ),
        RevertStatus::VIOLATED_REFERENTIAL_INTEGRITY => array(
            'class' => 'error',
            'message' => 'Error: Objects with missing references cannot be restored. For example we cannot restore comment where the related post was deleted.'
        ),
        RevertStatus::REVERTING_MERGE_COMMIT => array(
            'class' => 'error',
            'message' => 'Error: It is not possible to undo merge commit.'
        ),
    );

    $error = isset( $errors[ $_GET['error'] ] ) ? $errors[ $_GET['error'] ] : null;
}

if (isset($_GET['bug-report'])) {
    if ($_GET['bug-report'] === 'ok') {
        $error = array(
            'class' => 'updated',
            'message' => 'Bug report was sent. Thank you.'
        );
    } elseif ($_GET['bug-report'] === 'err') {
        $error = array(
            'class' => 'error',
            'message' => 'There was a problem with sending bug report. Please try it again. Thank you.'
        );
    }
}

if ( ! empty( $error ) ) {
    echo "<div class='$error[class]'><p>$error[message]</p></div>";
}

$displayServicePanel = false;
$displayServicePanel |= isset($_GET['bug-report']);
?>

<button id="vp-service-panel-button"><span class="icon icon-cog"></span></button>
<h2 id="vp-page-header">VersionPress</h2>

<div id="vp-service-panel" class="welcome-panel <?php if ($displayServicePanel) echo "displayed"; ?>">
    <h3>VersionPress Service Panel</h3>
    <h4>Bug report</h4>
    <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
        <input type="hidden" name="action" value="vp_send_bug_report">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">
                    <label for="vp-bug-email">Email</label>
                </th>

                <td>
                    <input type="email" value="" id="vp-bug-email" name="email">
                    <br>
                    <span class="description">We will respond you to this email.</span>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="vp-bug-description">Bug description</label>
                </th>

                <td>
                    <textarea rows="4" cols="22" id="vp-bug-description" name="description"></textarea>
                    <br>
                    <span class="description">Please tell us what you were doing when the bug occured.</span>
                </td>
            </tr>
            </tbody>
        </table>
        <p class="submit">
            <?php submit_button("Send bug report", "submit", "vp_send_bug_report", false); ?>
        </p>
    </form>
</div>

<?php
$showWelcomePanel = get_user_meta(get_current_user_id(), VersionPressOptions::USER_META_SHOW_WELCOME_PANEL, true);

if ($showWelcomePanel === "") {
?>

    <div id="welcome-panel" class="welcome-panel">

        <a id="vp-welcome-panel-close-button" class="welcome-panel-close" href="">Dismiss</a>

        <div class="welcome-panel-content">

            <h3>Welcome!</h3>

            <p class="about-description">Below is the main VersionPress table which will grow as changes are made to this site. You can <strong>Undo</strong> specific changes from the history or <strong>Roll back</strong> the site entirely to a previous state.</p>
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
    $repository = $versionPressContainer->resolve(VersionPressServices::REPOSITORY);

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
        <tr class=\"post-1 type-post status-publish format-standard hentry category-uncategorized alternate note level-0\">
            <td colspan=\"3\">VersionPress is not able to undo changes made before it has been activated.</td>
        </tr>
        ";

    if (!$isChildOfInitialCommit && $commits[0]->getHash() !== $initialCommitHash) {
        echo $disabledCommitsMessage;
    }

    foreach ($commits as $key => $commit) {
        $isChildOfInitialCommit = $isChildOfInitialCommit && ($commit->getHash() !== $initialCommitHash);
        $canUndoCommit = $isChildOfInitialCommit && !$commit->isMerge();
        $canRollbackToThisCommit = !$isFirstCommit && ($isChildOfInitialCommit || $commit->getHash() === $initialCommitHash);
        $commitDate = $commit->getDate()->format('d-M-y H:i:s');

        $changeInfo = ChangeInfoMatcher::buildChangeInfo($commit->getMessage());
        $undoSnippet = "<a " .
            "href='" . admin_url('admin.php?action=vp_show_undo_confirm&method=undo&commit=' . $commit->getHash()) . "' " .
            "class='vp-undo' " .
            "data-commit='" . $commit->getHash() . "' " .
            "data-commit-message=\"" . htmlspecialchars($changeInfo->getChangeDescription()) . "\"" .
            "style='text-decoration:none; white-space:nowrap;' " .
            "title='Reverts changes done by this commit'>Undo this</a>";

        $rollbackSnippet = "<a " .
            "href='" . admin_url('admin.php?action=vp_show_undo_confirm&method=rollback&commit=' . $commit->getHash()) . "' " .
            "class='vp-rollback' " .
            "data-commit='" . $commit->getHash() . "' " .
            "data-commit-date='" . $commitDate . "'" .
            "style='text-decoration:none; white-space:nowrap;' " .
            "title='Reverts site back to this state; effectively undos all the change up to this commit'>Roll back to this</a>";

        $versioningSnippet = "";
        if ($canUndoCommit) $versioningSnippet .= $undoSnippet;
        if ($canUndoCommit && $canRollbackToThisCommit) $versioningSnippet .= "&nbsp;|&nbsp;";
        if ($canRollbackToThisCommit) $versioningSnippet .= $rollbackSnippet;
        $isEnabled = $isChildOfInitialCommit || $canRollbackToThisCommit || $commit->getHash() === $initialCommitHash;

        $message = $changeInfo->getChangeDescription();
        echo "
        <tr class=\"post-1 type-post status-publish format-standard hentry category-uncategorized alternate level-0" . ($isEnabled ? "" : " disabled") . "\">
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
                if ($i > 0 && $lastNumber != $pageNumber-1) $divider = "&hellip;";
                elseif ($i > 0) $divider = "|";

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
