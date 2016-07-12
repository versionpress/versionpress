<?php

namespace VersionPress\Storages;

use Nette\Utils\Strings;
use VersionPress\ChangeInfos\ChangeInfoUtils;
use VersionPress\ChangeInfos\EntityChangeInfo;
use VersionPress\Database\EntityInfo;
use VersionPress\Git\ActionsInfo;

/**
 * Stores meta entities like postmeta and usermeta. It means that the meta entities are stored together
 * with their "parent" entities (e.g. postmeta is saved within the post). Meta entity is only key-value record
 * with a reference to the parent entity. The format for saving meta entities is key#vpid = value. This key
 * extended by VPID is called joined key.
 *
 * The MetaEntityStorage typically transforms the entity to the format metioned above and then saves it using
 * parent storage as a field of the parent entity.
 */
class MetaEntityStorage extends Storage
{
    private $lastVpId;

    protected $keyName;
    protected $valueName;
    private $parentReferenceName;

    /** @var Storage */
    private $parentStorage;
    /**
     * @var ActionsInfo
     */
    private $actionsInfo;

    public function __construct(Storage $parentStorage, EntityInfo $entityInfo, $dbPrefix, ActionsInfo $actionsInfo, $keyName = 'meta_key', $valueName = 'meta_value')
    {
        parent::__construct($entityInfo, $dbPrefix);
        $this->parentStorage = $parentStorage;
        $this->actionsInfo = $actionsInfo;
        $this->keyName = $keyName;
        $this->valueName = $valueName;
        $this->parentReferenceName = "vp_$entityInfo->parentReference";
    }

    public function save($data)
    {

        if (!$this->shouldBeSaved($data)) {
            return null;
        }

        $oldParent = $this->parentStorage->loadEntity($data[$this->parentReferenceName], null);
        $oldEntity = $this->extractEntityFromParentByVpId($oldParent, $data['vp_id']);

        $transformedData = $this->transformToParentEntityField($data);

        $this->lastVpId = $data['vp_id'];

        $this->parentStorage->save($transformedData);
        $newParent = $this->parentStorage->loadEntity($data[$this->parentReferenceName], null);
        $newEntity = $this->extractEntityFromParentByVpId($newParent, $data['vp_id']);

        if ($oldEntity == $newEntity) {
            return null;
        }

        if (!$oldEntity) {
            $action = 'create';
        } else {
            $action = 'edit';
        }

        return $this->createChangeInfoWithParentEntity($oldEntity, $newEntity, $oldParent, $newParent, $action);
    }

    public function delete($restriction)
    {
        $parentVpId = $restriction[$this->parentReferenceName];
        $parent = $this->parentStorage->loadEntity($parentVpId, null);
        $fieldToDelete = $this->getJoinedKeyByVpId($parent, $restriction['vp_id']);

        $oldEntity = $this->extractEntityFromParentByVpId($parent, $restriction['vp_id']);
        $oldParentEntity = $parent;

        $parent[$fieldToDelete] = false; // mark for deletion
        $newParentEntity = $parent;

        $this->parentStorage->save($parent);
        return $this->createChangeInfoWithParentEntity(
            $oldEntity,
            $oldEntity,
            $oldParentEntity,
            $newParentEntity,
            'delete'
        );
    }

    public function saveLater($data)
    {
        $transformedData = $this->transformToParentEntityField($data);
        $this->parentStorage->saveLater($transformedData);
    }

    public function commit()
    {
        $this->parentStorage->commit();
    }

    public function loadAll()
    {
        $parentEntities = $this->parentStorage->loadAll();
        $entities = [];

        foreach ($parentEntities as $parent) {
            foreach ($parent as $field => $value) {
                if (!Strings::contains($field, '#')) {
                    continue;
                }
                list ($key, $vpId) = explode('#', $field, 2);
                $entities[$vpId] = $this->extractEntityFromParentByVpId($parent, $vpId);
            }
        }

        return $entities;
    }

    public function exists($vpId, $parentId)
    {
        $parentExists = $this->parentStorage->exists($parentId, null);
        if (!$parentExists) {
            return false;
        }
        return (bool)$this->getJoinedKeyByVpId($this->parentStorage->loadEntity($parentId, null), $vpId);
    }

    public function getEntityFilename($vpId, $parentId)
    {
        return $this->parentStorage->getEntityFilename($parentId, null);
    }

    public function getPathCommonToAllEntities()
    {
        return $this->parentStorage->getPathCommonToAllEntities();
    }

    public function loadEntity($id, $parentId)
    {
        $parent = $this->parentStorage->loadEntity($parentId, null);
        return $this->extractEntityFromParentByVpId($parent, $id);
    }

    public function loadEntityByName($name, $parentId)
    {
        $parent = $this->parentStorage->loadEntity($parentId, null);
        return $this->extractEntityFromParentByName($parent, $name);
    }

    protected function createChangeInfo($oldParentEntity, $newParentEntity, $action)
    {
        $oldEntity = $this->extractEntityFromParentByVpId($oldParentEntity, $this->lastVpId);
        $newEntity = $this->extractEntityFromParentByVpId($newParentEntity, $this->lastVpId);
        return $this->createChangeInfoWithParentEntity(
            $oldEntity,
            $newEntity,
            $oldParentEntity,
            $newParentEntity,
            $action
        );
    }

    protected function createChangeInfoWithParentEntity($oldEntity, $newEntity, $oldParentEntity, $newParentEntity, $action)
    {
        $vpidColumnName = $this->entityInfo->vpidColumnName;

        $entityName = $this->entityInfo->entityName;
        $vpid = isset($newEntity[$vpidColumnName]) ? $newEntity[$vpidColumnName] : $oldEntity[$vpidColumnName];
        $automaticallySavedTags = $this->actionsInfo->getTags($entityName);
        $tags = ChangeInfoUtils::extractTags($automaticallySavedTags, $oldEntity, $newEntity);

        $changeInfo = new EntityChangeInfo($this->entityInfo, $this->actionsInfo, $action, $vpid, $tags, []);
        $files = $changeInfo->getChangedFiles();

        $action = apply_filters("vp_meta_entity_action_{$entityName}", $action, $oldEntity, $newEntity, $oldParentEntity, $newParentEntity);
        $tags = apply_filters("vp_meta_entity_tags_{$entityName}", $tags, $oldEntity, $newEntity, $action, $oldParentEntity, $newParentEntity);
        $files = apply_filters("vp_meta_entity_files_{$entityName}", $files, $oldEntity, $newEntity, $oldParentEntity, $newParentEntity);

        return new EntityChangeInfo($this->entityInfo, $this->actionsInfo, $action, $vpid, $tags, $files);
    }

    private function transformToParentEntityField($values)
    {
        $joinedKey = $this->createJoinedKey($values[$this->keyName], $values['vp_id']);

        $data = [
            'vp_id' => $values[$this->parentReferenceName],
            $joinedKey => $values[$this->valueName]
        ];
        return $data;
    }

    /**
     * Returns $key#$vpId from $key and $vpId inputs.
     * It's used in a parent entity file as key representing the entity.
     *
     * @param $key
     * @param $vpId
     * @return string
     */
    protected function createJoinedKey($key, $vpId)
    {
        return sprintf('%s#%s', $key, $vpId);
    }

    /**
     * Splits joined key $key#$vpId into array.
     * Example:
     * Let the key name is "meta_key" and the input is "some-key#1234",
     * then the output is array('meta_key' => 'some-key', 'vp_id' => '1234').
     *
     * @param $key
     * @return array
     */
    protected function splitJoinedKey($key)
    {
        $splittedKey = explode('#', $key, 2);
        return [
            $this->keyName => $splittedKey[0],
            'vp_id' => $splittedKey[1],
        ];
    }

    /**
     * Finds a joined key with given VPID within the parent entity.
     *
     * @param $parent
     * @param $vpId
     * @return string|null
     */
    private function getJoinedKeyByVpId($parent, $vpId)
    {
        foreach ($parent as $field => $value) {
            if (Strings::contains($field, $vpId)) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Finds a joined key with given VPID within the parent entity.
     *
     * @param $parent
     * @param $name
     * @return string|null
     */
    private function getJoinedKeyByName($parent, $name)
    {
        foreach ($parent as $field => $value) {
            if (Strings::startsWith($field, "$name#")) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @param $parentEntity
     * @param $vpId
     * @return array|null
     */
    protected function extractEntityFromParentByVpId($parentEntity, $vpId)
    {
        if (!$parentEntity) {
            return null;
        }

        $joinedKey = $this->getJoinedKeyByVpId($parentEntity, $vpId);

        if (!$joinedKey) {
            return null;
        }

        return $this->extractEntityFromParent($parentEntity, $joinedKey);
    }

    protected function extractEntityFromParentByName($parentEntity, $name)
    {
        if (!$parentEntity) {
            return null;
        }

        $joinedKey = $this->getJoinedKeyByName($parentEntity, $name);

        if (!$joinedKey) {
            return null;
        }

        return $this->extractEntityFromParent($parentEntity, $joinedKey);
    }

    private function extractEntityFromParent($parentEntity, $joinedKey)
    {
        $splittedKey = $this->splitJoinedKey($joinedKey);
        $entity = [
            $this->keyName => $splittedKey[$this->keyName],
            $this->valueName => $parentEntity[$joinedKey],
            'vp_id' => $splittedKey['vp_id'],
            $this->parentReferenceName => $parentEntity['vp_id'],
        ];

        return $entity;
    }

    public function shouldBeSaved($data)
    {
        return parent::shouldBeSaved($data)
        && isset($data[$this->parentReferenceName])
        && $this->parentStorage->exists($data[$this->parentReferenceName], null);
    }

    public function prepareStorage()
    {
    }
}
