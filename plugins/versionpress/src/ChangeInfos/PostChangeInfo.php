<?php
namespace VersionPress\ChangeInfos;

use Nette\Utils\Strings;
use VersionPress\Git\CommitMessage;

/**
 * Post changes.
 *
 * VP tags:
 *
 *     VP-Action: post/(create|edit|delete|trash|untrash|draft|publish)/VPID
 *     VP-Post-Title: Hello world
 *     VP-Post-Type: (post|page)
 *     VP-Post-UpdatedProperties: post_title,post_content,post_status
 */
class PostChangeInfo extends EntityChangeInfo {

    const POST_TITLE_TAG = "VP-Post-Title";
    const POST_TYPE_TAG = "VP-Post-Type";
    const POST_UPDATED_PROPERTIES_TAG = "VP-Post-UpdatedProperties";

    /**
     * Change in these properties create "Edited post" description instead of "Updated post".
     *
     * @var array
     */
    private static $CONTENT_PROPERTIES = array("post_content", "post_title");

    /**
     * Type of the post - "post" or "page"
     *
     * @var string
     */
    private $postType;

    /** @var string */
    private $postTitle;

    /**
     * Serialized array of updated post properties.
     *
     * @var string
     */
    private $postUpdatedProperties;

    public function __construct($action, $entityId, $postType, $postTitle, $postUpdatedProperties) {
        parent::__construct("post", $action, $entityId);
        $this->postType = $postType;
        $this->postTitle = $postTitle;
        $this->postUpdatedProperties = implode(",",$postUpdatedProperties);
    }

    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();

        $actionTag = $tags[TrackedChangeInfo::ACTION_TAG];
        list(, $action, $entityId) = explode("/", $actionTag, 3);

        $titleTag = isset($tags[self::POST_TITLE_TAG]) ? $tags[self::POST_TITLE_TAG] : $entityId;
        $type = isset($tags[self::POST_TYPE_TAG]) ? $tags[self::POST_TYPE_TAG] : "post";
        $updatedProperties = isset($tags[self::POST_UPDATED_PROPERTIES_TAG]) ? explode(",",$tags[self::POST_UPDATED_PROPERTIES_TAG]) : array();

        return new self($action, $entityId, $type, $titleTag, $updatedProperties);
    }

    public function getChangeDescription() {
        switch ($this->getAction()) {
            case "create":
                return "Created {$this->postType} '{$this->postTitle}'";
            case "trash":
                return Strings::capitalize($this->postType) . " '{$this->postTitle}' moved to trash";
            case "untrash":
                return Strings::capitalize($this->postType) . " '{$this->postTitle}' moved from trash";
            case "delete":
                return "Deleted {$this->postType} '{$this->postTitle}'";
            case "draft":
                return "Created draft for {$this->postType} '{$this->postTitle}'";
            case "publish":
                return "Published {$this->postType} '{$this->postTitle}'";
        }

        if(count(array_intersect(self::$CONTENT_PROPERTIES, explode(",",$this->postUpdatedProperties))) > 0) {
            return "Edited {$this->postType} '{$this->postTitle}'";
        } else {
            return "Updated {$this->postType} '{$this->postTitle}'";
        }
    }

    public function getChangedFiles() {
        $changes = parent::getChangedFiles();
        if ($this->postType !== "attachment") return $changes;

        $changes[] = array("type" => "path", "path" => WP_CONTENT_DIR . "/uploads/*");
        return $changes;
    }

    public function getCustomTags() {
        return array(
            self::POST_TITLE_TAG => $this->postTitle,
            self::POST_TYPE_TAG => $this->postType,
            self::POST_UPDATED_PROPERTIES_TAG => $this->postUpdatedProperties,
        );
    }

}
