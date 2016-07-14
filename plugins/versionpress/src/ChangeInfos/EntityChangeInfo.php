<?php

namespace VersionPress\ChangeInfos;

use VersionPress\Database\EntityInfo;
use VersionPress\Git\ActionsInfo;

/**
 * Base class for entity change infos like PostChangeInfo, CommentChangeInfo etc.
 * An entity is a database-tracked object that usually has a VPID (but not alwasy, see e.g. options).
 *
 * Derived ChangeInfos have these things in common:
 *
 * - The VP-Action tag value has the form of "entityName/action/entityId",
 *   e.g. "post/create/8F805A77ABC9485BA3F114E3E251E5FD" or "option/edit/blogname".
 *   Most commonly, the entityId is VPID.
 *
 * - Subclasses usually provide a set of VP tags to store additional info to commits, usually
 *   in the form of "VP-EntityType-Something: value", e.g. "VP-Post-Title: Hello world". These
 *   tags are used when the commit is read later and human-friendly message is rendered in the UI.
 *
 */
class EntityChangeInfo extends TrackedChangeInfo
{

    /** @var EntityInfo */
    private $entityInfo;

    /** @var string */
    private $action;

    /** @var string */
    private $entityId;

    /** @var array */
    private $customTags;

    /** @var array */
    private $customFiles;

    /** @var ActionsInfo */
    private $actionsInfo;

    /** @var string */
    private $commitMessageSubject;

    /**
     * @param EntityInfo $entityInfo
     * @param ActionsInfo $actionsInfo
     * @param string $action
     * @param string $entityId
     * @param array $customTags
     * @param array $customFiles
     */
    public function __construct($entityInfo, $actionsInfo, $action, $entityId, $customTags = [], $customFiles = [])
    {
        $this->entityInfo = $entityInfo;
        $this->action = $action;
        $this->entityId = $entityId;
        $this->customTags = $customTags;
        $this->customFiles = $customFiles;
    }

    public function getEntityName()
    {
        return $this->entityInfo->entityName;
    }

    /**
     * Action on the entity, used as the middle segment of the VP-Action tag. Usually
     * at least the "create", "edit" and "delete" actions are common to all the subclasses
     * but they may also add their own actions, like e.g. "trash" and "untrash" for posts.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Entity id - used as the last segment of VP-ActionTag. Usually a VPID but can
     * be also something else, e.g. a unique string in a WP table like in the `options` table.
     *
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    public function getChangeDescription()
    {
        if (empty($this->commitMessageSubject)) {
            $this->commitMessageSubject = $this->actionsInfo->createCommitMessage($this->getEntityName(), $this->getAction(), $this->getEntityId(), $this->getCustomTags());
        }

        return $this->commitMessageSubject;
    }

    /**
     * Used to construct a commit message body. This base class implementation is enough for all EntityChangeInfo
     * subclasses so thay don't override it. What they need to provide is the {@link getCustomTags()} implementation.
     *
     * @return string
     */
    protected function getActionTagValue()
    {
        return "{$this->getEntityName()}/{$this->getAction()}/{$this->getEntityId()}";
    }

    public function getCustomTags()
    {
        return $this->customTags;
    }

    public function getChangedFiles()
    {
        $change = [
            "type" => "storage-file",
            "entity" => $this->getEntityName(),
            "id" => $this->getEntityId(),
            "parent-id" => $this->getParentId()
        ];

        return array_merge([$change], $this->customFiles);
    }

    /**
     * Used by meta-entity storages as performance optimalization.
     * For example PostMetaChangeInfo returns VPID of related post.
     *
     * @return string|null
     */
    public function getParentId()
    {
        return null;
    }
}
