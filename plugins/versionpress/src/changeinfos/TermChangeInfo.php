<?php

/**
 * Term (categories, tags) changes.
 *
 * VP tags:
 *
 *     VP-Action: term/(create|edit|rename|delete)/VPID
 *     VP-Term-Name: Uncategorized
 *
 */
class TermChangeInfo extends EntityChangeInfo {

    const TERM_NAME_TAG = "VP-Term-Name";
    const TERM_OLD_NAME_TAG = "VP-Term-OldName";

    /** @var string */
    private $termName;

    /** @var string */
    private $oldTermName;

    public function __construct($action, $entityId, $termName, $oldTermName = null) {
        parent::__construct("term", $action, $entityId);
        $this->termName = $termName;
        $this->oldTermName = $oldTermName;
    }

    public function getChangeDescription() {
        if ($this->getAction() === "create")
            return "New term '{$this->termName}'";
        if ($this->getAction() === "rename")
            return "Term '{$this->oldTermName}' renamed to '{$this->termName}'";
        return "Edited term '{$this->termName}'";
    }

    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[TrackedChangeInfo::ACTION_TAG];
        list( , $action, $entityId) = explode("/", $actionTag, 3);
        $name = $tags[self::TERM_NAME_TAG];
        $oldName = isset($tags[self::TERM_OLD_NAME_TAG]) ? $tags[self::TERM_OLD_NAME_TAG] : null;
        return new self($action, $entityId, $name, $oldName);
    }

    protected function getCustomTags() {
        $tags = array(
            self::TERM_NAME_TAG => $this->termName
        );

        if ($this->getAction() === "rename") {
            $tags[self::TERM_OLD_NAME_TAG] = $this->oldTermName;
        }

        return $tags;
    }

}