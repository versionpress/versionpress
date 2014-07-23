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
        return new CommitMessage("VersionPress was installed", "VP-Action: versionpress/install");
    }

    /**
     * @param CommitMessage $commitMessage
     * @return boolean
     */
    public static function matchesCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        return isset($tags["VP-Action"]) && Strings::startsWith($tags["VP-Action"], "versionpress");
    }

    /**
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        return new self();
    }
}