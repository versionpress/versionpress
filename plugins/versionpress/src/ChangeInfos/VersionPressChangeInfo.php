<?php
namespace VersionPress\ChangeInfos;

use Nette\Utils\Strings;
use VersionPress\Git\CommitMessage;
use VersionPress\Utils\StringUtils;

/**
 * Represents VersionPress actions other than reverts (see {@link VersionPress\ChangeInfos\RevertChangeInfo}  for that).
 * It currently records only the "install" action and is probably the simplest of ChangeInfo types
 * as it doesn't capture any additional info.
 *
 * VP tags:
 *
 *     VP-Action: versionpress/install   <-- DEPRECATED, replaced by versionpress/activate
 *                versionpress/activate/1.0
 *                versionpress/deactivate
 *
 */
class VersionPressChangeInfo extends TrackedChangeInfo {


    private $action;
    private $versionPressVersion;


    /**
     * @param string $action
     * @param string $versionPressVersion
     */
    function __construct($action, $versionPressVersion = null) {
        $this->action = $action;
        $this->versionPressVersion = $versionPressVersion;
    }

    public function getEntityName() {
        return "versionpress";
    }

    public function getAction() {
        return $this->action;
    }

    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $actionTag = $commitMessage->getVersionPressTag(TrackedChangeInfo::ACTION_TAG);
        list(, $action, $versionPressVersion) = array_pad(explode("/", $actionTag, 3), 3, "");
        return new self($action, $versionPressVersion);
    }

    public function getChangeDescription() {

        switch ($this->action) {

            case "install":
                // Pre-1.0-beta2 message, see also WP-219
                return "Installed VersionPress";

            case "activate":
                return "Activated VersionPress " . $this->versionPressVersion;

            case "deactivate":
                return "Deactivated VersionPress";

            default:
                // just in case, this path shouldn't really be reached
                return Strings::capitalize(StringUtils::verbToPastTense($this->action)) . " VersionPress";

        }

    }

    protected function getActionTagValue() {
        $actionTag = "versionpress/$this->action";
        if ($this->versionPressVersion) {
            $actionTag .= "/" . $this->versionPressVersion;
        }
        return $actionTag;
    }

    public function getCustomTags() {
        return array();
    }

    public function getChangedFiles() {
        switch ($this->action) {
            case "deactivate":
                return array(
                    array("type" => "path", "path" => VP_VPDB_DIR . "/*"),
                    array("type" => "path", "path" => ABSPATH . WPINC . "/wp-db.php"),
                    array("type" => "path", "path" => ABSPATH . WPINC . "/wp-db.php.original"),
                );
            default:
                return array(array("type" => "path", "path" => "*"));
        }
    }
}
