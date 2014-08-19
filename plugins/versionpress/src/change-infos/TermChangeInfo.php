<?php

class TermChangeInfo extends EntityChangeInfo {

    const TERM_NAME_TAG = "VP-Term-Name";

    /**
     * @var string
     */
    private $termName;

    public function __construct($action, $entityId, $termName) {
        parent::__construct("term", $action, $entityId);
        $this->termName = $termName;
    }

    /**
     * @return string
     */
    public function getChangeDescription() {
        if($this->getAction() === "create")
            return "New term '{$this->termName}'";
        return "Edited term '{$this->termName}'";
    }

    public static function matchesCommitMessage(CommitMessage $commitMessage) {
        return parent::matchesCommitMessage($commitMessage) && ChangeInfoHelpers::actionTagStartsWith($commitMessage, "term");
    }

    /**
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[ChangeInfo::ACTION_TAG];
        list($_, $action, $entityId) = explode("/", $actionTag, 3);
        $nameTag = $tags[self::TERM_NAME_TAG];
        return new self($action, $entityId, $nameTag);
    }

    protected function getCustomTags() {
        return array(
            self::TERM_NAME_TAG => $this->termName
        );
    }

}