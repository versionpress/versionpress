<?php
namespace VersionPress\ChangeInfos;

use Nette\Utils\Strings;
use VersionPress\Git\CommitMessage;
use VersionPress\Utils\StringUtils;

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
class OptionChangeInfo extends EntityChangeInfo
{

    public function __construct($action, $entityId)
    {
        parent::__construct("option", $action, $entityId);
    }

    public function getChangeDescription()
    {
        $pastTense = StringUtils::verbToPastTense($this->getAction());
        return Strings::capitalize($pastTense) . " option '{$this->getEntityId()}'";
    }

    public static function buildFromCommitMessage(CommitMessage $commitMessage)
    {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[TrackedChangeInfo::ACTION_TAG];
        list(, $action, $entityId) = explode("/", $actionTag, 3);
        return new self($action, $entityId);
    }

    public function getCustomTags()
    {
        return [];
    }

    public function getChangedFiles()
    {

        $result = parent::getChangedFiles();
        if ($this->getEntityId() == "rewrite_rules") {
            $result[] = ["type" => "path", "path" => ABSPATH . ".htaccess"];
        }

        return $result;
    }
}
