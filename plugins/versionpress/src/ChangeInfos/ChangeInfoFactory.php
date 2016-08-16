<?php

namespace VersionPress\ChangeInfos;

use Nette\Utils\Strings;
use VersionPress\Actions\ActionsInfo;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Git\CommitMessage;

class ChangeInfoFactory
{
    /** @var DbSchemaInfo */
    private $dbSchema;
    /** @var ActionsInfo */
    private $actionsInfo;

    public function __construct(DbSchemaInfo $dbSchema, ActionsInfo $actionsInfo)
    {
        $this->dbSchema = $dbSchema;
        $this->actionsInfo = $actionsInfo;
    }

    public function createEntityChangeInfo($entity, $entityName, $action, $customTags = [], $customFiles = [])
    {
        $entityInfo = $this->dbSchema->getEntityInfo($entityName);
        $vpid = $entity[$entityInfo->vpidColumnName];

        $automaticallySavedTags = $this->actionsInfo->getTags($entityName);
        $tags = ChangeInfoUtils::extractTags($automaticallySavedTags, $entity, $entity);

        $tags = array_merge($tags, $customTags);

        return new EntityChangeInfo($entityInfo, $this->actionsInfo, $action, $vpid, $tags, $customFiles);
    }

    public function createTrackedChangeInfo($scope, $action, $entityId = null, $tags = [], $files = [])
    {
        return new TrackedChangeInfo($scope, $this->actionsInfo, $action, $entityId, $tags, $files);
    }

    public function buildChangeInfoEnvelopeFromCommitMessage(CommitMessage $commitMessage)
    {
        $fullBody = $commitMessage->getBody();
        $splittedBodies = explode("\n\n", $fullBody);
        $lastBody = $splittedBodies[count($splittedBodies) - 1];
        $changeInfoList = [];
        $version = null;
        $environment = null;

        if (self::containsVersion($lastBody)) {
            $version = self::extractTag(ChangeInfoEnvelope::VP_VERSION_TAG, $lastBody);
            $environment = self::extractTag(ChangeInfoEnvelope::VP_ENVIRONMENT_TAG, $lastBody);
            array_pop($splittedBodies);
        }

        if (!self::isTrackedChangeInfo($fullBody)) {
            return new ChangeInfoEnvelope([new UntrackedChangeInfo($commitMessage)], $version, $environment);
        }

        foreach ($splittedBodies as $body) {
            $partialCommitMessage = new CommitMessage("", $body);

            $actionTag = $partialCommitMessage->getVersionPressTag(TrackedChangeInfo::ACTION_TAG);
            list($scope, $action, $id) = explode('/', $actionTag, 3);

            $tags = $commitMessage->getVersionPressTags();
            unset($tags[TrackedChangeInfo::ACTION_TAG]);

            if ($this->dbSchema->isEntity($scope)) {
                $entityInfo = $this->dbSchema->getEntityInfo($scope);
                $changeInfoList[] = new EntityChangeInfo($entityInfo, $this->actionsInfo, $action, $id, $tags, []);
            } else {
                $changeInfoList[] = new TrackedChangeInfo($scope, $this->actionsInfo, $action, $id, $tags, []);
            }
        }

        return new ChangeInfoEnvelope($changeInfoList, $version, $environment);
    }

    private static function containsVersion($lastBody)
    {
        return Strings::startsWith($lastBody, ChangeInfoEnvelope::VP_VERSION_TAG);
    }

    private static function extractTag($tag, $commitMessageBody)
    {
        $tmpMessage = new CommitMessage("", $commitMessageBody);
        return $tmpMessage->getVersionPressTag($tag);
    }

    private static function isTrackedChangeInfo($body)
    {
        return Strings::startsWith($body, TrackedChangeInfo::ACTION_TAG);
    }
}
