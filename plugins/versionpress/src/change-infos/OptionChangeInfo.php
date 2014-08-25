<?php

class OptionChangeInfo extends EntityChangeInfo {

    const VALUE_TAG = "VP-Option-Value";
    /**
     * @var string
     */
    private $value;

    public function __construct($action, $entityId, $value = "") {
        parent::__construct("option", $action, $entityId);
        $this->value = $value;
    }

    public static function matchesCommitMessage(CommitMessage $commitMessage) {
        return parent::matchesCommitMessage($commitMessage) && ChangeInfoHelpers::actionTagStartsWith($commitMessage, "option");
    }


    /**
     * @return string
     */
    function getChangeDescription() {
        if($this->getAction() == "create")
            return "New option '{$this->getEntityId()}'";
        else
            return "Changed option '{$this->getEntityId()}'";
    }

    /**
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[BaseChangeInfo::ACTION_TAG];
        $value = isset($tags[self::VALUE_TAG]) ? $tags[self::VALUE_TAG] : "";
        list($_, $action, $entityId) = explode("/", $actionTag, 3);
        return new self($action, $entityId, $value);
    }

    protected function getCustomTags() {
        /*
         * There is no need to save serialized values.
         * It wouldn't be pretty to display some of these values in the log anyway.
         */
        if($this->value === "" || is_serialized($this->value)) return parent::getCustomTags();

        return array(
            self::VALUE_TAG => $this->value
        );
    }
}