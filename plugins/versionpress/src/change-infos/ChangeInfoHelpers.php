<?php

final class ChangeInfoHelpers {

    public static function actionTagStartsWith(CommitMessage $commitMessage, $prefix) {
        $tags = $commitMessage->getVersionPressTags();
        return isset($tags[ChangeInfo::ACTION_TAG]) && Strings::startsWith($tags[ChangeInfo::ACTION_TAG], $prefix);
    }

    private function __construct() {
    }
}