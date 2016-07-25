<?php

namespace VersionPress\ChangeInfos;

use Nette\Utils\Strings;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Git\ActionsInfo;
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

        if (count($splittedBodies) === 0 || $lastBody === "") {
            $changeInfoList[] = new UntrackedChangeInfo($commitMessage);
        }

        $specialTypes = [
            'composer' => ComposerChangeInfo::class,
            'theme' => ThemeChangeInfo::class,
            'plugin' => PluginChangeInfo::class,
            'translation' => TranslationChangeInfo::class,
            'wordpress' => WordPressUpdateChangeInfo::class,
            'versionpress/undo' => RevertChangeInfo::class,
            'versionpress/rollback' => RevertChangeInfo::class,
            'versionpress' => VersionPressChangeInfo::class,
        ];

        foreach ($splittedBodies as $body) {
            $partialCommitMessage = new CommitMessage("", $body);

            $actionTag = $partialCommitMessage->getVersionPressTag(TrackedChangeInfo::ACTION_TAG);

            /** @var ChangeInfo $matchingChangeInfoType */
            $matchingChangeInfoType = null;
            foreach ($specialTypes as $actionTagPrefix => $class) {
                if (Strings::startsWith($actionTag, $actionTagPrefix)) {
                    $matchingChangeInfoType = $class;
                }
            }

            if ($matchingChangeInfoType === null) {
                list($entityName, $action, $id) = explode('/', $actionTag, 3);
                $entityInfo = $this->dbSchema->getEntityInfo($entityName);
                $tags = $commitMessage->getVersionPressTags();
                unset($tags[TrackedChangeInfo::ACTION_TAG]);

                $changeInfoList[] = new EntityChangeInfo($entityInfo, $this->actionsInfo, $action, $id, $tags, []);
            } else {
                $changeInfoList[] = $matchingChangeInfoType::buildFromCommitMessage($partialCommitMessage, $this->dbSchema, $this->actionsInfo);
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
}
