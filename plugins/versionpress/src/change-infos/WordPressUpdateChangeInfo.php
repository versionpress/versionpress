<?php

/**
 * Change info about updating WordPress itself.
 *
 * Nitpicker's corner: the word "update" in the class name is OK, probably better upgrade,
 * see the frequency (and the title) of the {@link http://codex.wordpress.org/Updating_WordPress Updating WordPress}
 * topic in Codex.
 *
 * VP tags:
 *
 *     VP-Action: wordpress/update/4.0
 */
class WordPressUpdateChangeInfo extends TrackedChangeInfo {

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
        $actionTag = $tags[TrackedChangeInfo::ACTION_TAG];
        list($_, $__, $version) = explode("/", $actionTag, 3);
        return new self($version);
    }

    /**
     * @return string
     */
    function getChangeDescription() {
        return "WordPress updated to version " . $this->getVersion();
    }

    protected function constructActionTagValue() {
        return "{$this->getObjectType()}/{$this->getAction()}/{$this->getVersion()}";
    }

    protected function getCustomTags() {
        return array();
    }


}