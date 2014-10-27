<?php

/**
 * Base class for entity change infos like PostChangeInfo, CommentChangeInfo etc.
 * An entity is a database-tracked object that has a VPID.
 *
 * Derived ChangeInfos have these things in common:
 *
 * - The VP-Action tag value has the form of "entityType/action/entityId",
 *   e.g. "post/create/8F805A77ABC9485BA3F114E3E251E5FD" or "option/edit/blogname".
 * - Subclasses usually provide a set of VP tags to store additional info, usually
 *   in the form of "VP-EntityType-Something: value", e.g. "VP-Post-Title: Hello world"
 * - Because of the point above, subclasses don't implement the constructActionTagValue()
 *   method (base implementation in this class is enough).
 */
abstract class EntityChangeInfo extends TrackedChangeInfo {

    /** @var string */
    private $entityType;

    /** @var string */
    private $action;

    /** @var int */
    private $entityId;

    /**
     * @param string $entityType Entity type, used for the first segment of VP-Action tag
     * @param string $action Action, the middle segment of the VP-Action tag
     * @param string $entityId VPID, the last segment od the VP-Action tag
     */
    public function __construct($entityType, $action, $entityId) {
        $this->entityType = $entityType;
        $this->action = $action;
        $this->entityId = $entityId;
    }

    /**
     * Entity type like "post", "comment" etc. Used as the first segment of VP-Action tags.
     *
     * @return string
     */
    public function getObjectType() {
        return $this->entityType;
    }

    /**
     * Action on the entity, used as the middle segment of the VP-Action tag. Usually
     * at least the "create", "edit" and "delete" actions are common to all the subclasses
     * but they may also add their own actions, like e.g. "trash" and "untrash" for posts.
     *
     * @return string
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Entity id - used as the last segment of VP-ActionTag. Usually a VPID but can
     * be also something else, e.g. a unique string in a WP table, see `options` table.
     *
     * @return int
     */
    public function getEntityId() {
        return $this->entityId;
    }

    protected function constructActionTagValue() {
        return "{$this->getObjectType()}/{$this->getAction()}/{$this->getEntityId()}";
    }
}