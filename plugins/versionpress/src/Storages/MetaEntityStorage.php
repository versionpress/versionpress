<?php

namespace VersionPress\Storages;


use Nette\Utils\Strings;
use VersionPress\Database\EntityInfo;

abstract class MetaEntityStorage extends Storage {

    private $lastVpId;

    private $keyName;
    private $valueName;
    private $parentReferenceName;

    /** @var Storage */
    private $parentStorage;


    function __construct(Storage $parentStorage, $keyName, $valueName, $parentReferenceName) {
        $this->parentStorage = $parentStorage;
        $this->keyName = $keyName;
        $this->valueName = $valueName;
        $this->parentReferenceName = $parentReferenceName;
    }

    public function save($data) {

        if (!$this->shouldBeSaved($data)) {
            return null;
        }

        $oldParent = $this->parentStorage->loadEntity($this->getParentVpId($data['vp_id']));
        $oldEntity = $this->extractEntityFromParent($oldParent, $data['vp_id']);

        $transformedData = $this->transformToParentEntityField($data);

        $this->lastVpId = $data['vp_id'];

        $this->parentStorage->save($transformedData);
        $newParent = $this->parentStorage->loadEntity($this->getParentVpId($data['vp_id']));
        $newEntity = $this->extractEntityFromParent($newParent, $data['vp_id']);

        if (!$oldEntity) {
            $action = 'create';
        } else {
            $action = 'edit';
        }

        return $this->createChangeInfoWithParentEntity($oldEntity, $newEntity, $oldParent, $newParent, $action);
    }

    public function saveAll($entities) {
        foreach ($entities as $entity) {
            $data = $this->transformToParentEntityField($entity);
            $this->parentStorage->save($data);
        }
    }

    public function delete($restriction) {
        $parentVpId = $restriction[$this->parentReferenceName];
        $parent = $this->parentStorage->loadEntity($parentVpId);
        $fieldToDelete = $this->getJoinedKeyByVpId($parent, $restriction['vp_id']);

        $oldEntity = $this->extractEntityFromParent($parent, $restriction['vp_id']);
        $oldParentEntity = $parent;

        $parent[$fieldToDelete] = false; // mark for deletion
        $newParentEntity = $parent;

        $this->parentStorage->save($parent);
        return $this->createChangeInfoWithParentEntity($oldEntity, null, $oldParentEntity, $newParentEntity, 'delete');
    }

    public function loadAll() {
        $parentEntities =  $this->parentStorage->loadAll();
        $entities = array();

        foreach ($parentEntities as $parent) {
            foreach ($parent as $field => $value) {
                if (!Strings::contains($field, '#')) {
                    continue;
                }
                list ($key, $vpId) = explode('#', $field, 2);
                $entities[] = $this->extractEntityFromParent($parent, $vpId);
            }

        }

        return $entities;
    }

    public function getEntityFilename($vpId) {
        $parentVpId = $this->getParentVpId($vpId);
        return $this->parentStorage->getEntityFilename($parentVpId);
    }

    protected function isExistingEntity($vpId) {
        return (bool)$this->loadEntity($vpId);
    }

    public function loadEntity($vpid) {
        $parentVpId = $this->getParentVpId($vpid);
        $parent = $this->parentStorage->loadEntity($parentVpId);
        return $this->extractEntityFromParent($parent, $vpid);
    }

    protected function createChangeInfo($oldParentEntity, $newParentEntity, $action) {
        $oldEntity = $this->extractEntityFromParent($oldParentEntity, $this->lastVpId);
        $newEntity = $this->extractEntityFromParent($newParentEntity, $this->lastVpId);
        return $this->createChangeInfoWithParentEntity($oldEntity, $newEntity, $oldParentEntity, $newParentEntity, $action);
    }

    protected abstract function createChangeInfoWithParentEntity($oldEntity, $newEntity, $oldParentEntity, $newParentEntity, $action);

    private function transformToParentEntityField($values) {
        $joinedKey = $this->createJoinedKey($values[$this->keyName], $values['vp_id']);

        $data = array(
            'vp_id' => $values[$this->parentReferenceName],
            $joinedKey => $values[$this->valueName]
        );
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
    private function createJoinedKey($key, $vpId) {
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
    private function splitJoinedKey($key) {
        $splittedKey = explode('#', $key, 2);
        return array(
            $this->keyName => $splittedKey[0],
            'vp_id' => $splittedKey[1],
        );
    }

    private function getJoinedKeyByVpId($parent, $vpId) {
        foreach ($parent as $field => $value) {
            if (Strings::contains($field, $vpId)) {
                return $field;
            }
        }

        return null;
    }

    private function getParentVpId($vpid) {
        $entities = $this->parentStorage->loadAll();
        foreach ($entities as $entity) {
            foreach ($entity as $field => $value) {
                if (Strings::contains($field, $vpid)) {
                    return $entity['vp_id'];
                }
            }
        }
        return null;
    }

    /**
     * @param $parentEntity
     * @param $vpId
     * @return array|null
     */
    protected function extractEntityFromParent($parentEntity, $vpId) {
        if (!$parentEntity) {
            return null;
        }

        $joinedKey = $this->getJoinedKeyByVpId($parentEntity, $vpId);

        if (!$joinedKey) {
            return null;
        }

        $splittedKey = $this->splitJoinedKey($joinedKey);
        $entity = array(
            $this->keyName => $splittedKey[$this->keyName],
            $this->valueName => $parentEntity[$joinedKey],
            'vp_id' => $splittedKey['vp_id'],
            $this->parentReferenceName => $parentEntity['vp_id'],
        );

        return $entity;
    }

    function shouldBeSaved($data) {
        return true;
    }

    function prepareStorage() {
    }
}