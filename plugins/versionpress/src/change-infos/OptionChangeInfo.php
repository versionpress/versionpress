<?php

class OptionChangeInfo extends EntityChangeInfo {

    public function __construct($action, $entityId) {
        parent::__construct("option", $action, $entityId);
    }

    public static function matchesCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        return parent::matchesCommitMessage($commitMessage) && Strings::startsWith($tags["VP-Action"], "option");
    }


    /**
     * @return string
     */
    function getChangeDescription() {
        return "Changed option " . $this->getEntityId();
    }

    /**
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags["VP-Action"];
        list($_, $action, $entityId) = explode("/", $actionTag, 3);
        return new self($action, $entityId);
    }
}