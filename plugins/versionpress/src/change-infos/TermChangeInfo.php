<?php

/**
 * Term (categories, tags) changes.
 *
 * VP tags:
 *
 *     VP-Action: term/(create|edit|delete)/VPID
 *     VP-Term-Name: Uncategorized
 *
 * TODO: the list of tags should grow, see WP-141.
 *
 */
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

    public function getChangeDescription() {
        if($this->getAction() === "create")
            return "New term '{$this->termName}'";
        return "Edited term '{$this->termName}'";
    }

    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[TrackedChangeInfo::ACTION_TAG];
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