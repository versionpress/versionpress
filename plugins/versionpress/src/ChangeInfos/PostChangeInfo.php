<?php
namespace VersionPress\ChangeInfos;

use NStrings;
use VersionPress\Git\CommitMessage;

/**
 * Post changes.
 *
 * VP tags:
 *
 *     VP-Action: post/(create|edit|delete|trash|untrash|draft|publish)/VPID
 *     VP-Post-Title: Hello world
 *     VP-Post-Type: (post|page)
 */
class PostChangeInfo extends EntityChangeInfo {

    const POST_TITLE_TAG = "VP-Post-Title";
    const POST_TYPE_TAG = "VP-Post-Type";

    /**
     * Type of the post - "post" or "page"
     *
     * @var string
     */
    private $postType;

    /** @var string */
    private $postTitle;

    public function __construct($action, $entityId, $postType, $postTitle) {
        parent::__construct("post", $action, $entityId);
        $this->postType = $postType;
        $this->postTitle = $postTitle;
    }

    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();

        $actionTag = $tags[TrackedChangeInfo::ACTION_TAG];
        list(, $action, $entityId) = explode("/", $actionTag, 3);

        $titleTag = isset($tags[self::POST_TITLE_TAG]) ? $tags[self::POST_TITLE_TAG] : $entityId;
        $type = isset($tags[self::POST_TYPE_TAG]) ? $tags[self::POST_TYPE_TAG] : "post";

        return new self($action, $entityId, $type, $titleTag);
    }

    public function getChangeDescription() {
        switch ($this->getAction()) {
            case "create":
                return "Created {$this->postType} '{$this->postTitle}'";
            case "trash":
                return NStrings::capitalize($this->postType) . " '{$this->postTitle}' moved to trash";
            case "untrash":
                return NStrings::capitalize($this->postType) . " '{$this->postTitle}' moved from trash";
            case "delete":
                return "Deleted {$this->postType} '{$this->postTitle}'";
            case "draft":
                return "Created draft for {$this->postType} '{$this->postTitle}'";
            case "publish":
                return "Published {$this->postType} '{$this->postTitle}'";
        }
        return "Edited {$this->postType} '{$this->postTitle}'";
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
            self::POST_TYPE_TAG => $this->postType
        );
    }

}
