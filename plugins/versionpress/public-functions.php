<?php

use VersionPress\ChangeInfos\TrackedChangeInfo;
use VersionPress\DI\VersionPressServices;
use VersionPress\Git\ActionsInfo;
use VersionPress\Git\Committer;

function vp_force_action($scope, $action, $id = '', $tags = [], $files = [])
{
    global $versionPressContainer;
    /** @var Committer $committer */
    $committer = $versionPressContainer->resolve(VersionPressServices::COMMITTER);
    /** @var ActionsInfo $actionsInfo */
    $actionsInfo = $versionPressContainer->resolve(VersionPressServices::ACTIONS_INFO);

    $changeInfo = new TrackedChangeInfo($scope, $actionsInfo, $action, $id, $tags, $files);
    $committer->forceChangeInfo($changeInfo);
}
