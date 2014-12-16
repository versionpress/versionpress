<?php

/**
 * Option changes like creating or updating them.
 *
 * VP tags:
 *
 *     VP-Action: option/(create|edit|delete)/<name>
 *
 * Examples:
 *
 *     VP-Action: option/create/test_option
 *     VP-Action: option/edit/blogname
 *     VP-Action: option/delete/test_option
 *
 * Note: there was an intention to use VP-Option-Value tag before but it was never implemented and
 * it is not clear how to approach this. See WP-147.
 */
class OptionChangeInfo extends EntityChangeInfo {

    public function __construct($action, $entityId) {
        parent::__construct("option", $action, $entityId);
    }

    function getChangeDescription() {

        $messages = array(
            "create" => "New option '{$this->getEntityId()}'",
            "edit" => "Changed option '{$this->getEntityId()}'",
            "delete" => "Deleted option '{$this->getEntityId()}'"
        );

        return $messages[$this->getAction()];

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

    public function getChangedFiles() {

        $result = parent::getChangedFiles();
        if ($this->getEntityId() == "rewrite_rules") {
            $result[] = array("type" => "path", "path" => ABSPATH . ".htaccess");
        }
        
        return $result;
    }
}
