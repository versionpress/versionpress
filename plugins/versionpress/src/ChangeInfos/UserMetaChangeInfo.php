<?php
namespace VersionPress\ChangeInfos;

use VersionPress\Git\CommitMessage;

/**
 * Changes of certain user properties like nickname etc.
 *
 * VP tags:
 *
 *     VP-Action: usermeta/(create|edit|delete)/VPID
 *
 * TODO this is work in progress at the time of writing this documentation, see WP-130
 *
 * @see UserChangeInfo
 */
class UserMetaChangeInfo extends EntityChangeInfo {

    const USER_LOGIN = "VP-User-Login";
    const USER_META_KEY = "VP-UserMeta-Key";

    /** @var string */
    private $userLogin;

    /** @var string */
    private $userMetaKey;

    public function __construct($action, $entityId, $userLogin, $userMetaKey) {
        parent::__construct("usermeta", $action, $entityId);
        $this->userLogin = $userLogin;
        $this->userMetaKey = $userMetaKey;
    }

    public function getChangeDescription() {
        if ($this->getAction() === "create")
            return "New option '{$this->userMetaKey}' for user '{$this->userLogin}'";
        return "Edited option '{$this->userMetaKey}' for user '{$this->userLogin}'";
    }

    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[TrackedChangeInfo::ACTION_TAG];
        $userMetaKey = $tags[self::USER_META_KEY];
        $userLogin = $tags[self::USER_LOGIN];
        list(, $action, $entityId) = explode("/", $actionTag);
        return new self($action, $entityId, $userLogin, $userMetaKey);
    }

    public function getCustomTags() {
        return array(
            self::USER_LOGIN => $this->userLogin,
            self::USER_META_KEY => $this->userMetaKey
        );
    }


}
