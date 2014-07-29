<?php

class OptionChangeInfo extends EntityChangeInfo {

    public function __construct($action, $entityId) {
        parent::__construct("option", $action, $entityId);
    }

    public static function matchesCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        return parent::matchesCommitMessage($commitMessage) && Strings::startsWith($tags[ChangeInfo::ACTION_TAG], "option");
    }


    /**
     * @return string
     */
    function getChangeDescription() {
        if($this->getAction() == "create")
            return "New option \"{$this->getEntityId()}\"";
        else
            return "Changed option \"{$this->getEntityId()}\"";
    }

    /**
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[ChangeInfo::ACTION_TAG];
        list($_, $action, $entityId) = explode("/", $actionTag, 3);
        return new self($action, $entityId);
    }
}