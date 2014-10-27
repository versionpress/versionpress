<?php

/**
 * Change info about undos and rollbacks. Other VersionPress action
 * that are not undos or rollbacks are represented using {@link VersionPressChangeInfo}.
 *
 * VP tags:
 *
 *     VP-Action: versionpress/(undo|rollback)/HASH123
 *
 * (No additional tags are required, even the ranges, when we support them, will be part
 * of the hash part of the main VP-Action tag.)
 *
 */
class RevertChangeInfo extends TrackedChangeInfo {

    const OBJECT_TYPE = "versionpress";
    const ACTION_UNDO = "undo";
    const ACTION_ROLLBACK = "rollback";

    /**
     * @var string
     */
    private $action;
    /**
     * @var string
     */
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

    /**
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        list($_, $action, $commitHash) = explode("/", $tags[TrackedChangeInfo::ACTION_TAG], 3);
        return new self($action, $commitHash);
    }

    /**
     * @return string
     */
    public function getChangeDescription() {
        return ($this->action == self::ACTION_UNDO ? "Reverted change " : "Rollback to ") . $this->commitHash;
    }

    /**
     * Returns the first line of commit message
     *
     * @return string
     */
    protected function constructCommitMessageHead() {
        return ($this->action == self::ACTION_UNDO ? "Reverted change " : "Rollback to ") . $this->commitHash;
    }

    /**
     * Returns the content of VP-Action tag
     *
     * @return string
     */
    protected function constructActionTagValue() {
        return sprintf("%s/%s/%s", self::OBJECT_TYPE, $this->getAction(), $this->commitHash);
    }
}