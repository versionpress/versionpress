<?php

use VersionPress\Actions\ActionsInfoProvider;
use VersionPress\ChangeInfos\TrackedChangeInfo;
use VersionPress\DI\VersionPressServices;
use VersionPress\Git\Committer;

function vp_force_action($scope, $action, $id = '', $tags = [], $files = [])
{
    global $versionPressContainer;
    /** @var Committer $committer */
    $committer = $versionPressContainer->resolve(VersionPressServices::COMMITTER);
    /** @var ActionsInfoProvider $actionsInfoProvider */
    $actionsInfoProvider = $versionPressContainer->resolve(VersionPressServices::ACTIONSINFO_PROVIDER_ACTIVE_PLUGINS);
    $actionsInfo = $actionsInfoProvider->getActionsInfo($scope);

    $changeInfo = new TrackedChangeInfo($scope, $actionsInfo, $action, $id, $tags, $files);
    $committer->forceChangeInfo($changeInfo);
}
