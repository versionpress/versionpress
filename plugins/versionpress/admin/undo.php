<?php
    use VersionPress\Git\GitRepository;
    use VersionPress\Git\Reverter;
    use VersionPress\DI\VersionPressServices;
    use VersionPress\ChangeInfos\ChangeInfoMatcher;

    if(!in_array($_GET['method'], array('undo', 'rollback'))) { exit(); }

    global $versionPressContainer;
    /** @var GitRepository $repository */
    $repository = $versionPressContainer->resolve(VersionPressServices::REPOSITORY);
    /** @var Reverter $reverter */
    $reverter = $versionPressContainer->resolve(VersionPressServices::REVERTER);

    $canRevert = $reverter->canRevert();
    $commit = $repository->getCommit($_GET['commit']);
    $changeInfo = ChangeInfoMatcher::buildChangeInfo($commit->getMessage());

    $method = $_GET['method'];

    $title = ($method == 'undo')
        ? "<div class='title-content'>Undo <em>{$changeInfo->getChangeDescription()}</em> ?</div>"
        : "<div class='title-content'>Roll back to <em>{$commit->getDate()->format('d-M-y H:i:s')}</em> ?</div>";

    $message = "
        <p>
            For EAP releases, please have a backup.
            <a href='http://docs.versionpress.net/en/feature-focus/undo-and-rollback' target='_blank'>
                Learn more about reverts.
            </a>
        </p>";

    $errors = ( !$canRevert ? "
        <p class='undo-warning'>
            <span class='icon icon-warning'></span>
            You have <a href='http://docs.versionpress.net/en/feature-focus/undo-and-rollback#uncommitted-files' target='_blank'>uncommitted changes</a> in your WordPress directory.<br>
            Please commit them before doing a revert.
        </p>" : "");

    $buttonProceed = "<a " .
        "class='button " . ( !$canRevert ? "disabled" : "") . "' " .
        "id='popover-ok-button' ".
        "href='" . ( !$canRevert ? "javascript:;" : admin_url('admin.php?action=vp_' . $method . '&commit=' . $commit->getHash()) ) . "'>Proceed</a>";
    $buttonCancel = "<a " .
        "class='button cancel' ".
        "id='popover-cancel-button' ".
        "href='" . (vp_is_ajax() ? "javascript:;" : menu_page_url('versionpress', false)) . "'>Cancel</a>";

    $body = "
        <div>
            {$message}
            {$errors}
            <div class='button-container'>
                {$buttonProceed}
                {$buttonCancel}
            </div>
        </div>
    ";

    if (vp_is_ajax()) {
        $response = new stdClass();
        $response->body = $body;
        echo json_encode($response);
        wp_die();
    } else {
        echo "<h3>{$title}</h3>";
        echo $body;
    }
