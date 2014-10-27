<?php

/**
 * Represents VersionPress actions other than reverts (see {@link RevertChangeInfo}  for that).
 * It currently records only the `install` action and is probably the simplest of ChangeInfo types
 * as it doesn't capture any additional information.
 *
 * VP tags:
 *
 *     VP-Action: versionpress/install
 *
 */
class VersionPressChangeInfo extends TrackedChangeInfo {

    /**
     * @inheritdoc
     * @return string
     */
    public function getObjectType() {
        return "versionpress";
    }

    /**
     * @inheritdoc
     * @return string
     */
    public function getAction() {
        return "install";
    }

    /**
     * @inheritdoc
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        return new self();
    }

    /**
     * @inheritdoc
     * @return string
     */
    public function getChangeDescription() {
        return "Installed VersionPress";
    }

    /**
     * @inheritdoc
     * @return string
     */
    protected function constructCommitMessageHead() {
        return $this->getChangeDescription();
    }

    /**
     * @inheritdoc
     * @return string
     */
    protected function constructActionTagValue() {
        return "versionpress/install";
    }
}