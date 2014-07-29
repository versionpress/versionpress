<?php

/**
 * Class used for commit representing installation of VersionPress
 */
class VersionPressChangeInfo implements ChangeInfo {

    /**
     * @return string
     */
    public function getObjectType() {
        return "";
    }

    /**
     * @return string
     */
    public function getAction() {
        return "";
    }

    /**
     * @return CommitMessage
     */
    public function getCommitMessage() {
        return new CommitMessage("VersionPress was installed", ChangeInfo::ACTION_TAG . ": versionpress/install");
    }

    /**
     * @param CommitMessage $commitMessage
     * @return boolean
     */
    public static function matchesCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        return isset($tags[ChangeInfo::ACTION_TAG]) && Strings::startsWith($tags[ChangeInfo::ACTION_TAG], "versionpress");
    }

    /**
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        return new self();
    }

    /**
     * @return string
     */
    function getChangeDescription() {
        return "Installed VersionPress";
    }
}