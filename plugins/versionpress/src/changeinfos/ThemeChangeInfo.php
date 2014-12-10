<?php

/**
 * Theme changes like installation, switching, editing etc.
 *
 * VP tags:
 *
 *     VP-Action: theme/(install|update|customize|edit|switch|delete)/twentyfourteen
 *     VP-Theme-Name: Twenty Fourteen
 *
 * Note: theme is `customize`d via the WP customizer, `edit`ed via the built in text editor.
 *
 */
class ThemeChangeInfo extends TrackedChangeInfo {

    private static $OBJECT_TYPE = "theme";
    const THEME_NAME_TAG = "VP-Theme-Name";

    /** @var string */
    private $themeId;

    /** @var string */
    private $themeName;

    /** @var string */
    private $action;

    /**
     * @param string $themeId E.g. "twentyfourteen"
     * @param string $action One of the supported actions, see this class's docs
     * @param string $themeName If not provided, found automatically based on $themeId
     */
    public function __construct($themeId, $action, $themeName = null) {
        $this->themeId = $themeId;
        $this->action = $action;

        if ($themeName == null) {
            $themes = wp_get_themes();
            $themeName = $themes[$themeId]->name;
        }

        $this->themeName = $themeName;
    }

    public function getObjectType() {
        return self::$OBJECT_TYPE;
    }

    public function getAction() {
        return $this->action;
    }

    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $actionTag = $commitMessage->getVersionPressTag(TrackedChangeInfo::ACTION_TAG);
        $themeName = $commitMessage->getVersionPressTag(self::THEME_NAME_TAG);
        list( , $action, $themeId) = explode("/", $actionTag, 3);
        return new self($themeId, $action, $themeName);
    }

    public function getChangeDescription() {

        if ($this->action === 'switch') {
            return "Theme switched to '{$this->themeName}'";
        }

        return NStrings::capitalize($this->action) . (NStrings::endsWith($this->action, "e") ? "d" : "ed") . " theme '{$this->themeName}'";
    }

    /**
     * Reports changes in files that relate to given ChangeInfo. Used in Committer
     * to commit only related files.
     * Returns data in this format:
     *
     * add  =>   [
     *             [ type => "storage-file",
     *               entity => "post",
     *               id => <VPID> ],
     *             [ type => "path",
     *               path => C:/www/wp/wp-content/upload/* ],
     *           ],
     * delete => [
     *             [ type => "storage-file",
     *               entity => "user",
     *               id => <VPID> ],
     *             ...
     *           ]
     *
     * @return array
     */
    public function getChangedFiles() {
        $changeType = $this->getAction() === "delete" ? "delete" : "add";
        $themeChange = array("type" => "path", "path" => $path = WP_CONTENT_DIR . "/themes/" . $this->themeId . "/*");
        $optionChange = array("type" => "storage-file", "entity" => "option", "id" => "");
        return array($changeType => array($themeChange, $optionChange));
    }

    protected function getActionTagValue() {
        return "{$this->getObjectType()}/{$this->getAction()}/" . $this->themeId;
    }

    protected function getCustomTags() {
        return array(
            self::THEME_NAME_TAG => $this->themeName
        );
    }
}