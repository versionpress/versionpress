<?php

final class ChangeInfoHelpers {

    public static function actionTagStartsWith(CommitMessage $commitMessage, $prefix) {
        $tags = $commitMessage->getVersionPressTags();
        return isset($tags[TrackedChangeInfo::ACTION_TAG]) && NStrings::startsWith($tags[TrackedChangeInfo::ACTION_TAG], $prefix);
    }

    private function __construct() {
    }
}