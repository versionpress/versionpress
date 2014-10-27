<?php

/**
 * User changes.
 *
 * VP tags:
 *
 *     VP-Action: user/(create|edit|delete)/VPID
 *     VP-User-Login: testuser
 */
class UserChangeInfo extends EntityChangeInfo {

    const USER_LOGIN = "VP-User-Login";

    /**
     * @var string
     */
    private $userLogin;

    public function __construct($action, $entityId, $userLogin) {
        parent::__construct("user", $action, $entityId);
        $this->userLogin = $userLogin;
    }

    public function getChangeDescription() {
        if($this->getAction() === "create")
            return "New user '{$this->userLogin}'";
        return "Edited user '{$this->userLogin}'";
    }

    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[TrackedChangeInfo::ACTION_TAG];
        $userLogin = $tags[self::USER_LOGIN];
        list($_, $action, $entityId) = explode("/", $actionTag);
        return new self($action, $entityId, $userLogin);
    }

    protected function getCustomTags() {
        return array(
            self::USER_LOGIN => $this->userLogin
        );
    }


}