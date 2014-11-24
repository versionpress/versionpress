<?php

/**
 * Represents VersionPress actions other than reverts (see {@link RevertChangeInfo}  for that).
 * It currently records only the "install" action and is probably the simplest of ChangeInfo types
 * as it doesn't capture any additional info.
 *
 * VP tags:
 *
 *     VP-Action: versionpress/install
 *
 */
class VersionPressChangeInfo extends TrackedChangeInfo {

    public function getObjectType() {
        return "versionpress";
    }

    public function getAction() {
        return "install";
    }

    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        return new self();
    }

    public function getChangeDescription() {
        return "Installed VersionPress";
    }

    protected function getActionTagValue() {
        return "versionpress/install";
    }

    protected function getCustomTags() {
        return array();
    }

    /**
     * Reports changes in files that relate to given ChangeInfo. Used in Committer
     * to commit only related files.
     * Returns data in this format:
     *
     * add  =>   [
     *             [ type => "storage-file",
     *               entity => "post",
     *               id => <VPID> ],
     *             [ type => "path",
     *               path => C:/www/wp/wp-content/upload/* ],
     *           ],
     * delete => [
     *             [ type => "storage-file",
     *               entity => "user",
     *               id => <VPID> ],
     *             ...
     *           ]
     *
     * @return array
     */
    public function getChangedFiles() {
        return array("add" => array(array("type" => "path", "path" => "*")));
    }
}