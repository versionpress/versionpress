<?php
    use VersionPress\Git\GitRepository;
    use VersionPress\DI\VersionPressServices;
    use VersionPress\ChangeInfos\ChangeInfoMatcher;

    if(!in_array($_GET['method'], array('undo', 'rollback'))) { exit(); }

    global $versionPressContainer;
    /** @var GitRepository $repository */
    $repository = $versionPressContainer->resolve(VersionPressServices::REPOSITORY);
    $clearWorkingDirectory = $repository->getStatus() == null;
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

    $errors = ( !$clearWorkingDirectory ? "<p class='error'>Please commit your changes</p>" : "");

    $buttonProceed = "<a " .
        "class='button " . ( !$clearWorkingDirectory ? "disabled" : "") . "' " .
        "href='" . ( !$clearWorkingDirectory ? "javascript:;" : admin_url('admin.php?action=vp_' . $method . '&commit=' . $commit->getHash()) ) . "'>Proceed</a>";
    $buttonCancel = "<a " .
        "class='button cancel' ".
        "href='" . (isAjax() ? "javascript:;" : admin_url('admin.php?page=versionpress/admin/index.php')) . "'>Cancel</a>";

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

    if(isAjax()) {
        $response = new stdClass();
        $response->body = $body;
        echo json_encode($response);
        wp_die();
    } else {
        echo "<h3>{$title}</h3>";
        echo $body;
    }