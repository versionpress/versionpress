<?php

class WordPressUpdateChangeInfo implements ChangeInfo {

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
     * @return CommitMessage
     */
    public function getCommitMessage() {
        $messageHead = "WordPress updated to version " . $this->version;
        $messageBody = ChangeInfo::ACTION_TAG . ": {$this->getObjectType()}/{$this->getAction()}/" . $this->version;
        return new CommitMessage($messageHead, $messageBody);
    }

    /**
     * @param CommitMessage $commitMessage
     * @return boolean
     */
    public static function matchesCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        return isset($tags[ChangeInfo::ACTION_TAG]) && Strings::startsWith($tags[ChangeInfo::ACTION_TAG], self::OBJECT_TYPE);
    }

    /**
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[ChangeInfo::ACTION_TAG];
        list($_, $__, $version) = explode("/", $actionTag, 3);
        return new self($version);
    }

    /**
     * @return string
     */
    function getChangeDescription() {
        return "Wordpress updated to version " . $this->getVersion();
    }
}