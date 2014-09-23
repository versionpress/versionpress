<?php

class ThemeChangeInfo extends BaseChangeInfo {

    private static $OBJECT_TYPE = "theme";
    const THEME_NAME_TAG = "VP-Theme-Name";

    /** @var string */
    private $themeId;

    /** @var string */
    private $themeName;
    /**
     * Values: switch / install
     * @var string
     */
    private $action;

    public function __construct($themeId, $action, $themeName = null) {
        $this->themeId = $themeId;
        $this->action = $action;

        if($themeName == null) {
            $themes = wp_get_themes();
            $themeName = $themes[$themeId]->name;
        }

        $this->themeName = $themeName;
    }

    /**
     * @return string
     */
    public function getObjectType() {
        return self::$OBJECT_TYPE;
    }

    /**
     * @return string
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * @param CommitMessage $commitMessage
     * @return boolean
     */
    public static function matchesCommitMessage(CommitMessage $commitMessage) {
        return ChangeInfoHelpers::actionTagStartsWith($commitMessage, "theme");
    }

    /**
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $actionTag = $commitMessage->getVersionPressTag(BaseChangeInfo::ACTION_TAG);
        $themeName = $commitMessage->getVersionPressTag(self::THEME_NAME_TAG);
        list($_, $action, $themeId) = explode("/", $actionTag, 3); // maybe slug
        return new self($themeId, $action, $themeName);
    }

    /**
     * @return string
     */
    public function getChangeDescription() {
        if($this->action === 'switch') return "Theme switched to '{$this->themeName}'";
        return NStrings::capitalize($this->action) . (NStrings::endsWith($this->action, "e") ? "d" : "ed") . " theme '{$this->themeName}'";
    }

    /**
     * @return string
     */
    protected function getActionTag() {
        return "{$this->getObjectType()}/{$this->getAction()}/" . $this->themeId;
    }

    /**
     * Returns the first line of commit message
     *
     * @return string
     */
    protected function getCommitMessageHead() {
        return $this->getChangeDescription();
    }

    protected function getCustomTags() {
        return array(
            self::THEME_NAME_TAG => $this->themeName
        );
    }
}