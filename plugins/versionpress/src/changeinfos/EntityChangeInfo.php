<?php

/**
 * Base class for entity change infos like PostChangeInfo, CommentChangeInfo etc.
 * An entity is a database-tracked object that usually has a VPID (but not alwasy, see e.g. options).
 *
 * Derived ChangeInfos have these things in common:
 *
 * - The VP-Action tag value has the form of "entityType/action/entityId",
 *   e.g. "post/create/8F805A77ABC9485BA3F114E3E251E5FD" or "option/edit/blogname".
 *   Most commonly, the entityId is VPID.
 *
 * - Subclasses usually provide a set of VP tags to store additional info to commits, usually
 *   in the form of "VP-EntityType-Something: value", e.g. "VP-Post-Title: Hello world". These
 *   tags are used when the commit is read later and human-friendly message is rendered in the UI.
 *
 */
abstract class EntityChangeInfo extends TrackedChangeInfo {

    /** @var string */
    private $entityType;

    /** @var string */
    private $action;

    /** @var string */
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
     * be also something else, e.g. a unique string in a WP table like in the `options` table.
     *
     * @return string
     */
    public function getEntityId() {
        return $this->entityId;
    }

    /**
     * Used to construct a commit message body. This base class implementation is enough for all EntityChangeInfo subclasses
     * so thay don't override it. What they need to provide is the {@link getCustomTags()} implementation.
     *
     * @return string
     */
    protected function getActionTagValue() {
        return "{$this->getObjectType()}/{$this->getAction()}/{$this->getEntityId()}";
    }

    /**
     * Reports changes in files that relate to given ChangeInfo. Used in Committer
     * to commit only related files.
     * Returns data in this format:
     *
     * add  =>   [
     *             [ type => "storage-file",
     *               entity => "post",
     *               id => <VPID> ],
     *             [ type => "path",
     *               path => C:/www/wp/wp-content/upload/* ],
     *           ],
     * delete => [
     *             [ type => "storage-file",
     *               entity => "user",
     *               id => <VPID> ],
     *             ...
     *           ]
     *
     * @return array
     */
    public function getChangedFiles() {
        $changeType = $this->getAction() === "delete" ? "delete" : "add";
        $change = array(
            "type" => "storage-file",
            "entity" => $this->getObjectType(),
            "id" => $this->getEntityId()
        );

        return array($changeType => array($change));
    }
}