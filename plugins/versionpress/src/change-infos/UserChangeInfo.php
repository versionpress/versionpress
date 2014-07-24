<?php

class UserChangeInfo extends EntityChangeInfo {

    /**
     * @var string
     */
    private $userLogin;

    public function __construct($action, $entityId, $userLogin) {
        parent::__construct("user", $action, $entityId);
        $this->userLogin = $userLogin;
    }

    /**
     * @return string
     */
    public function getChangeDescription() {
        if($this->getAction() === "create")
            return "New user \"{$this->userLogin}\"";
    }

    public static function matchesCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        return parent::matchesCommitMessage($commitMessage) && Strings::startsWith($tags["VP-Action"], "user");
    }

    /**
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags["VP-Action"];
        $userLogin = $tags["VP-User-Login"];
        list($_, $action, $entityId) = explode("/", $actionTag);
        return new self($action, $entityId, $userLogin);
    }

    protected function getCustomTags() {
        return array("VP-User-Login" => $this->userLogin);
    }


}