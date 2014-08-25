<?php

class WordPressUpdateChangeInfo extends BaseChangeInfo {

    const OBJECT_TYPE = "wordpress";
    const ACTION = "update";

    /** @var string */
    private $version;

    public function __construct($version) {
        $this->version = $version;
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
        return self::ACTION;
    }

    /**
     * @return string
     */
    public function getVersion() {
        return $this->version;
    }

    /**
     * @param CommitMessage $commitMessage
     * @return boolean
     */
    public static function matchesCommitMessage(CommitMessage $commitMessage) {
        return ChangeInfoHelpers::actionTagStartsWith($commitMessage, self::OBJECT_TYPE);
    }

    /**
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[BaseChangeInfo::ACTION_TAG];
        list($_, $__, $version) = explode("/", $actionTag, 3);
        return new self($version);
    }

    /**
     * @return string
     */
    function getChangeDescription() {
        return "Wordpress updated to version " . $this->getVersion();
    }

    /**
     * Returns the first line of commit message
     *
     * @return string
     */
    protected function getCommitMessageHead() {
        return "WordPress updated to version " . $this->getVersion();
    }

    /**
     * Returns the content of VP-Action tag
     *
     * @return string
     */
    protected function getActionTag() {
        return "{$this->getObjectType()}/{$this->getAction()}/{$this->getVersion()}";
    }
}