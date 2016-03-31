<?php
namespace VersionPress\ChangeInfos;

use VersionPress\Git\CommitMessage;

/**
 * Changes of certain user properties like nickname etc.
 *
 * VP tags:
 *
 *     VP-Action: usermeta/(create|edit|delete)/VPID
 *     VP-User-Login: johndoe
 *     VP-UserMeta-Key: some_meta
 *     VP-User-Id: VPID
 *
 *
 * @see UserChangeInfo
 */
class UserMetaChangeInfo extends EntityChangeInfo {

    const USER_LOGIN = "VP-User-Login";
    const USER_META_KEY = "VP-UserMeta-Key";
    const USER_VPID_TAG = "VP-User-Id";

    /** @var string */
    private $userLogin;

    /** @var string */
    private $userMetaKey;

    /** @var string */
    private $userVpId;

    public function __construct($action, $entityId, $userLogin, $userMetaKey, $userVpId) {
        parent::__construct("usermeta", $action, $entityId);
        $this->userLogin = $userLogin;
        $this->userMetaKey = $userMetaKey;
        $this->userVpId = $userVpId;
    }

    public function getChangeDescription() {
        if ($this->getAction() === "create") {
            return "New user-meta '{$this->userMetaKey}' for user '{$this->userLogin}'";
        }

        if ($this->getAction() === "delete") {
            return "Deleted user-meta '{$this->userMetaKey}' for user '{$this->userLogin}'";
        }

        return "Edited user-meta '{$this->userMetaKey}' for user '{$this->userLogin}'";
    }

    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[TrackedChangeInfo::ACTION_TAG];
        $userMetaKey = $tags[self::USER_META_KEY];
        $userLogin = $tags[self::USER_LOGIN];
        $userVpId = $tags[self::USER_VPID_TAG];
        list(, $action, $entityId) = explode("/", $actionTag);
        return new self($action, $entityId, $userLogin, $userMetaKey, $userVpId);
    }

    public function getCustomTags() {
        return array(
            self::USER_LOGIN => $this->userLogin,
            self::USER_META_KEY => $this->userMetaKey,
            self::USER_VPID_TAG => $this->userVpId
        );
    }

    public function getParentId() {
        return $this->userVpId;
    }
}
