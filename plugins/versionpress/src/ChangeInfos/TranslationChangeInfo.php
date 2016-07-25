<?php

namespace VersionPress\ChangeInfos;

use Nette\Utils\Strings;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Git\ActionsInfo;
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
class TranslationChangeInfo extends TrackedChangeInfo
{

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
    public function __construct($action, $languageCode, $languageName, $type = 'core', $name = null)
    {
        $this->action = $action;
        $this->languageCode = $languageCode ? $languageCode : 'en_US';
        $this->languageName = $languageName;
        $this->type = $type;
        $this->name = $name;
    }

    public function getScope()
    {
        return self::$OBJECT_TYPE;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getLanguageCode()
    {
        return $this->languageCode;
    }

    public function getChangeDescription()
    {
        if ($this->action === 'activate') {
            return "Site language switched to '{$this->languageName}'";
        }

        $pastTense = StringUtils::verbToPastTense($this->action);
        return Strings::capitalize($pastTense) . " translation '{$this->languageName}'";
    }

    protected function getActionTagValue()
    {
        return "{$this->getScope()}/{$this->getAction()}";
    }

    public function getCustomTags()
    {
        return [
            self::LANGUAGE_CODE_TAG => $this->languageCode,
            self::LANGUAGE_NAME_TAG => $this->languageName,
            self::TRANSLATION_TYPE_TAG => $this->type,
            self::TRANSLATION_NAME_TAG => $this->name
        ];
    }

    public function getChangedFiles()
    {
        $path = WP_CONTENT_DIR . "/languages/";

        if ($this->type === "core") {
            $path .= "*";
        } else {
            $path .= $this->type . "s/" . $this->name . "-" . $this->languageCode . ".*";
        }

        $filesChange = ["type" => "path", "path" => $path];

        $optionChange = ["type" => "all-storage-files", "entity" => "option"];

        return [$filesChange, $optionChange];
    }
}
