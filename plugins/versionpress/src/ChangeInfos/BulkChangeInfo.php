<?php

namespace VersionPress\ChangeInfos;

use Nette\NotSupportedException;
use Nette\Utils\Strings;
use VersionPress\Git\CommitMessage;
use VersionPress\Utils\StringUtils;

abstract class BulkChangeInfo implements ChangeInfo {

    /** @var TrackedChangeInfo[] */
    protected $changeInfos;
    /** @var int */
    protected $count;

    /**
     * @param TrackedChangeInfo[] $changeInfos
     */
    public function __construct(array $changeInfos) {
        $this->changeInfos = $changeInfos;
        $this->count = count($changeInfos);
    }

    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        throw new NotSupportedException("Building bulk changeinfo from commit message is not supported");
    }

    public function getCommitMessage() {
        throw new NotSupportedException("Commit message is created in ChangeInfoEnvelope from original objects");
    }

    /**
     * Returns original ChangeInfo objects.
     *
     * @return TrackedChangeInfo[]
     */
    public function getChangeInfos() {
        return $this->changeInfos;
    }

    public function getChangeDescription() {
        return sprintf("%s %d %s",
            Strings::capitalize(StringUtils::verbToPastTense($this->getAction())),
            $this->count,
            StringUtils::pluralize($this->getEntityName()));
    }

    public function getAction() {
        return $this->changeInfos[0]->getAction();
    }

    private function getEntityName() {
        return $this->changeInfos[0]->getEntityName();
    }
}