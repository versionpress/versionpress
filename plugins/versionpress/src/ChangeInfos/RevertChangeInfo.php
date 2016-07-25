<?php
namespace VersionPress\ChangeInfos;

use VersionPress\Database\DbSchemaInfo;
use VersionPress\DI\VersionPressServices;
use VersionPress\Git\ActionsInfo;
use VersionPress\Git\CommitMessage;
use VersionPress\Git\GitRepository;

/**
 * Change info about undos and rollbacks. Other VersionPress action
 * that are not undos or rollbacks are represented using {@link VersionPress\ChangeInfos\VersionPressChangeInfo}.
 *
 * VP tags:
 *
 *     VP-Action: versionpress/(undo|rollback)/hash123
 *
 * (No additional tags are required, even the ranges, when we support them, will be part
 * of the hash part of the main VP-Action tag.)
 *
 */
class RevertChangeInfo extends TrackedChangeInfo
{

    const OBJECT_TYPE = "versionpress";
    const ACTION_UNDO = "undo";
    const ACTION_ROLLBACK = "rollback";

    /** @var string */
    private $action;

    /** @var string */
    private $commitHash;

    public function __construct($action, $commitHash)
    {
        $this->action = $action;
        $this->commitHash = $commitHash;
    }

    public function getEntityName()
    {
        return self::OBJECT_TYPE;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getCommitHash()
    {
        return $this->commitHash;
    }

    public function getChangeDescription()
    {
        global $versionPressContainer; // temporary solution todo: find better way to pass the dependency
        /** @var GitRepository $repository */
        $repository = $versionPressContainer->resolve(VersionPressServices::REPOSITORY);
        $revertedCommit = $repository->getCommit($this->commitHash);

        if ($this->action === self::ACTION_UNDO) {
            $revertedChangeInfo = ChangeInfoMatcher::buildChangeInfo($revertedCommit->getMessage());
            return sprintf("Reverted change \"%s\"", $revertedChangeInfo->getChangeDescription());
        }

        return sprintf("Rollback to the same state as of %s", $revertedCommit->getDate()->format('d-M-y H:i:s'));
    }

    protected function getActionTagValue()
    {
        return sprintf("%s/%s/%s", self::OBJECT_TYPE, $this->getAction(), $this->commitHash);
    }

    public function getCustomTags()
    {
        return [];
    }

    public function getChangedFiles()
    {
        return [["type" => "path", "path" => "*"]];
    }
}
