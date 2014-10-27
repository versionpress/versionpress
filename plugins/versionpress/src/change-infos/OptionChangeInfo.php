<?php

/**
 * Option changes like creating or updating entities from the `options` table.
 *
 * VP tags:
 *
 *     VP-Action: option/(create|edit|delete)/blogname
 *
 * Note: there used to be a VP-Option-Value tag before but we don't use it any more as it doesn't make
 * much sense - the data change is captured in the commit body and we don't need to store it in a tag.
 */
class OptionChangeInfo extends EntityChangeInfo {

    public function __construct($action, $entityId) {
        parent::__construct("option", $action, $entityId);
    }

    function getChangeDescription() {
        if($this->getAction() == "create") {
            return "New option '{$this->getEntityId()}'";
        } else {
            return "Changed option '{$this->getEntityId()}'";
        }
    }

    static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[TrackedChangeInfo::ACTION_TAG];
        list($_, $action, $entityId) = explode("/", $actionTag, 3);
        return new self($action, $entityId);
    }

    protected function getCustomTags() {
        return array();
    }
}