<?php

class RevertChangeInfo extends BaseChangeInfo {

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
        return $this->action;
    }

    /**
     * @param CommitMessage $commitMessage
     * @return boolean
     */
    public static function matchesCommitMessage(CommitMessage $commitMessage) {
        return ChangeInfoHelpers::actionTagStartsWith($commitMessage, self::OBJECT_TYPE . "/" . self::ACTION_UNDO)
            || ChangeInfoHelpers::actionTagStartsWith($commitMessage, self::OBJECT_TYPE . "/" . self::ACTION_ROLLBACK);
    }

    /**
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        list($_, $action, $commitHash) = explode("/", $tags[BaseChangeInfo::ACTION_TAG], 3);
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
    protected function getCommitMessageHead() {
        return ($this->action == self::ACTION_UNDO ? "Reverted change " : "Rollback to ") . $this->commitHash;
    }

    /**
     * Returns the content of VP-Action tag
     *
     * @return string
     */
    protected function getActionTag() {
        return sprintf("%s/%s/%s", self::OBJECT_TYPE, $this->getAction(), $this->commitHash);
    }
}