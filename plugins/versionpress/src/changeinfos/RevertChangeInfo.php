<?php

/**
 * Change info about undos and rollbacks. Other VersionPress action
 * that are not undos or rollbacks are represented using {@link VersionPressChangeInfo}.
 *
 * VP tags:
 *
 *     VP-Action: versionpress/(undo|rollback)/hash123
 *
 * (No additional tags are required, even the ranges, when we support them, will be part
 * of the hash part of the main VP-Action tag.)
 *
 */
class RevertChangeInfo extends TrackedChangeInfo {

    const OBJECT_TYPE = "versionpress";
    const ACTION_UNDO = "undo";
    const ACTION_ROLLBACK = "rollback";

    /** @var string */
    private $action;

    /** @var string */
    private $commitHash;

    function __construct($action, $commitHash) {
        $this->action = $action;
        $this->commitHash = $commitHash;
    }

    public function getObjectType() {
        return self::OBJECT_TYPE;
    }

    public function getAction() {
        return $this->action;
    }

    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        list( , $action, $commitHash) = explode("/", $tags[TrackedChangeInfo::ACTION_TAG], 3);
        return new self($action, $commitHash);
    }

    public function getChangeDescription() {
        return ($this->action == self::ACTION_UNDO ? "Reverted change " : "Rollback to ") . $this->commitHash;
    }

    protected function getActionTagValue() {
        return sprintf("%s/%s/%s", self::OBJECT_TYPE, $this->getAction(), $this->commitHash);
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