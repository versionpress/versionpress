<?php

namespace VersionPress\ChangeInfos;
use Nette\Utils\Strings;
use VersionPress\Git\CommitMessage;
use VersionPress\Utils\StringUtils;

/**
 * Translation changes like switching, updating etc.
 *
 * VP tags:
 *
 *     VP-Action: translation/(activate|install|update|uninstall)
 *     VP-Language-Code: en_US
 *     VP-Language-Name: English (United States)
 *     VP-Translation-Type: (core|theme|plugin)
 *     VP-Translation-Name: akismet
 *
 */
class TranslationChangeInfo extends TrackedChangeInfo {

    private static $OBJECT_TYPE = "translation";
    const LANGUAGE_CODE_TAG = "VP-Language-Code";
    const LANGUAGE_NAME_TAG = "VP-Language-Name";
    const TRANSLATION_TYPE_TAG = "VP-Translation-Type";
    const TRANSLATION_NAME_TAG = "VP-Translation-Name";

    /** @var string */
    private $action;

    /** @var string */
    private $languageCode;

    /** @var string */
    private $languageName;

    /** @var string */
    private $type;

    /** @var string */
    private $name;

    /**
     * @param string $action See VP-Action tag documentation in the class docs
     * @param string $languageCode Code of the translation language
     * @param string $languageName The translation language
     * @param string $type See VP-Translation-Type tag documentation in the class docs
     * @param string $name Additional name information for types plugin, theme
     */
    public function __construct($action, $languageCode, $languageName,  $type = 'core', $name = null) {
        $this->action = $action;
        $this->languageCode = $languageCode ? $languageCode : 'en_US';
        $this->languageName = $languageName;
        $this->type = $type;
        $this->name = $name;
    }

    public function getEntityName() {
        return self::$OBJECT_TYPE;
    }

    public function getAction() {
        return $this->action;
    }

    public function getLanguageCode() {
        return $this->languageCode;
    }

    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $actionTag = $commitMessage->getVersionPressTag(TrackedChangeInfo::ACTION_TAG);
        $languageCode = $commitMessage->getVersionPressTag(self::LANGUAGE_CODE_TAG);
        $languageName = $commitMessage->getVersionPressTag(self::LANGUAGE_NAME_TAG);
        $type = $commitMessage->getVersionPressTag(self::TRANSLATION_TYPE_TAG);
        $name = $commitMessage->getVersionPressTag(self::TRANSLATION_NAME_TAG);
        list(, $action) = explode("/", $actionTag, 2);
        return new self($action, $languageCode, $languageName, $type, $name);
    }

    public function getChangeDescription() {
        if ($this->action === 'activate') {
            return "Language switched to '{$this->languageName}'";
        }

        return Strings::capitalize(StringUtils::verbToPastTense($this->action)) . " translation '{$this->languageName}'";
    }

    protected function getActionTagValue() {
        return "{$this->getEntityName()}/{$this->getAction()}";
    }

    public function getCustomTags() {
        return array(
            self::LANGUAGE_CODE_TAG => $this->languageCode,
            self::LANGUAGE_NAME_TAG => $this->languageName,
            self::TRANSLATION_TYPE_TAG => $this->type,
            self::TRANSLATION_NAME_TAG => $this->name
        );
    }

    public function getChangedFiles() {
        $path = WP_CONTENT_DIR . "/languages/";

        if ($this->type === "core") {
            $path .= "*";
        } else {
            $path .= $this->type . "s/" . $this->name . "-" . $this->languageCode . ".*";
        }

        $filesChange = array("type" => "path", "path" => $path);

        $optionChange = array("type" => "storage-file", "entity" => "option", "id" => "", "parent-id" => "");

        return array($filesChange, $optionChange);
    }
}
