<?php

class UserMetaChangeInfo extends EntityChangeInfo {

    const USER_LOGIN = "VP-User-Login";
    const USER_META_KEY = "VP-UserMeta-Key";
    /**
     * @var string
     */
    private $userLogin;

    /**
     * @var string
     */
    private $userMetaKey;

    public function __construct($action, $entityId, $userLogin, $userMetaKey) {
        parent::__construct("usermeta", $action, $entityId);
        $this->userLogin = $userLogin;
        $this->userMetaKey = $userMetaKey;
    }

    /**
     * @return string
     */
    public function getChangeDescription() {
        if($this->getAction() === "create")
            return "New option '{$this->userMetaKey}' for user '{$this->userLogin}'";
        return "Edited option '{$this->userMetaKey}' for user '{$this->userLogin}'";
    }

    public static function matchesCommitMessage(CommitMessage $commitMessage) {
        return parent::matchesCommitMessage($commitMessage) && ChangeInfoHelpers::actionTagStartsWith($commitMessage, "usermeta");
    }

    /**
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[BaseChangeInfo::ACTION_TAG];
        $userMetaKey = $tags[self::USER_META_KEY];
        $userLogin = $tags[self::USER_LOGIN];
        list($_, $action, $entityId) = explode("/", $actionTag);
        return new self($action, $entityId, $userLogin, $userMetaKey);
    }

    protected function getCustomTags() {
        return array(
            self::USER_LOGIN => $this->userLogin,
            self::USER_META_KEY => $this->userMetaKey
        );
    }


}