<?php

namespace VersionPress\ChangeInfos;


use Nette\Utils\Strings;
use VersionPress\Git\CommitMessage;
use VersionPress\Utils\StringUtils;

class CommentMetaChangeInfo extends EntityChangeInfo {

    const COMMENT_META_KEY = "VP-CommentMeta-Key";
    const COMMENT_VPID_TAG = "VP-Comment-Id";

    /** @var string */
    private $commentVpId;

    /** @var string */
    private $metaKey;

    public function __construct($action, $entityId, $commentVpId, $metaKey) {
        parent::__construct("commentmeta", $action, $entityId);
        $this->commentVpId = $commentVpId;
        $this->metaKey = $metaKey;
    }

    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[TrackedChangeInfo::ACTION_TAG];
        list(, $action, $entityId) = explode("/", $actionTag, 3);
        $metaKey = $tags[self::COMMENT_META_KEY];
        $commentVpid = $tags[self::COMMENT_VPID_TAG];
        return new self($action, $entityId, $commentVpid, $metaKey);
    }

    public function getChangeDescription() {
        if ($this->getAction() === "create") {
            return "New comment-meta '{$this->metaKey}' created";
        }

        if ($this->getAction() === "delete") {
            return "Deleted comment-meta '{$this->metaKey}'";
        }

        return "Edited comment-meta '{$this->metaKey}'";
    }

    public function getCustomTags() {
        return array(
            self::COMMENT_META_KEY => $this->metaKey,
            self::COMMENT_VPID_TAG => $this->commentVpId
        );
    }

    public function getParentId() {
        return $this->commentVpId;
    }
}
