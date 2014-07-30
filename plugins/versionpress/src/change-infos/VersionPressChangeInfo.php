<?php

/**
 * Class used for commit representing installation of VersionPress
 */
class VersionPressChangeInfo implements ChangeInfo {

    const OBJECT_TYPE = "versionpress";
    const ACTION = "install"; // there are no other actions handled by this change info yet

    /**
     * @return string
     */
    public function getObjectType() {
        return self::OBJECT_TYPE;
    }

    /**
     * @return string
     */
    public function getAction() {
        return self::ACTION;
    }

    /**
     * @return CommitMessage
     */
    public function getCommitMessage() {
        return new CommitMessage("VersionPress was installed",  sprintf("%s: %s/%s", ChangeInfo::ACTION_TAG, self::OBJECT_TYPE, self::ACTION));
    }

    /**
     * @param CommitMessage $commitMessage
     * @return boolean
     */
    public static function matchesCommitMessage(CommitMessage $commitMessage) {
        return ChangeInfoHelpers::actionTagStartsWith($commitMessage, self::OBJECT_TYPE . "/" . self::ACTION);
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