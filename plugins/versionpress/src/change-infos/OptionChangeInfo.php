<?php

/**
 * Option changes like creating or updating them.
 *
 * VP tags:
 *
 *     VP-Action: option/(create|edit|delete)/blogname
 *
 * Note: there was an intention to use VP-Option-Value tag before but it was never implemented and
 * it is not clear how to approach this. See WP-147.
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
        list( , $action, $entityId) = explode("/", $actionTag, 3);
        return new self($action, $entityId);
    }

    protected function getCustomTags() {
        return array();
    }
}