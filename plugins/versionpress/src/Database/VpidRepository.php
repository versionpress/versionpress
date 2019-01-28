<?php

namespace VersionPress\Database;

use Nette\Utils\Strings;
use VersionPress\DI\VersionPressServices;
use VersionPress\Utils\IdUtil;
use VersionPress\Utils\ReferenceUtils;
use VersionPress\Utils\SerializedDataCursor;

class VpidRepository
{
    const UNKNOWN_VPID_MARK = '<unknown-vpid>';

    /** @var Database */
    private $database;
    /** @var DbSchemaInfo */
    private $schemaInfo;

    public function __construct($database, DbSchemaInfo $schemaInfo)
    {
        $this->database = $database;
        $this->schemaInfo = $schemaInfo;
    }

    /**
     * Returns VPID of entity of given type and id.
     *
     * @param $entityName
     * @param $id
     * @return null|string
     */
    public function getVpidForEntity($entityName, $id)
    {
        $tableName = $this->schemaInfo->getTableName($entityName);
        return $this->database->get_var("SELECT HEX(vp_id) FROM {$this->database->vp_id} " .
            "WHERE id = '$id' AND `table` = '$tableName'");
    }

    public function getIdForVpid($vpid)
    {
        return intval($this->database->get_var("SELECT id FROM {$this->database->vp_id} WHERE vp_id = UNHEX('$vpid')"));
    }

    public function replaceForeignKeysWithReferences($entityName, $entity)
    {
        $entityInfo = $this->schemaInfo->getEntityInfo($entityName);

        foreach ($entityInfo->references as $referenceName => $targetEntity) {
            if (isset($entity[$referenceName])) {
                if ($this->isNullReference($entity[$referenceName])) {
                    $referenceVpids = 0;
                } else {
                    $referenceVpids = $this->replaceIdsInString($targetEntity, $entity[$referenceName]);
                }

                $entity['vp_' . $referenceName] = $referenceVpids;
                unset($entity[$referenceName]);
            }
        }

        foreach ($entityInfo->valueReferences as $referenceName => $targetEntity) {
            list($sourceColumn, $sourceValue, $valueColumn, $pathInStructure) =
                array_values(ReferenceUtils::getValueReferenceDetails($referenceName));

            if (isset($entity[$sourceColumn]) && ($entity[$sourceColumn] === $sourceValue ||
                    ReferenceUtils::valueMatchesWildcard($sourceValue, $entity[$sourceColumn]))
                && isset($entity[$valueColumn])
            ) {
                if ($this->isNullReference($entity[$valueColumn])) {
                    continue;
                }

                if ($targetEntity[0] === '@') {
                    $entityNameProvider = substr($targetEntity, 1);
                    $targetEntity = call_user_func($entityNameProvider, $entity);
                    if (!$targetEntity) {
                        continue;
                    }
                }

                if ($pathInStructure) {
                    $paths = ReferenceUtils::getMatchingPathsInSerializedData($entity[$valueColumn], $pathInStructure);
                } else {
                    $paths = [[]]; // root = the value itself
                }

                /** @var SerializedDataCursor[] $cursors */
                $cursors = array_map(function ($path) use (&$entity, $valueColumn) {
                    return new SerializedDataCursor($entity[$valueColumn], $path);
                }, $paths);

                foreach ($cursors as $cursor) {
                    $ids = $cursor->getValue();
                    $referenceVpids = $this->replaceIdsInString($targetEntity, $ids);
                    $cursor->setValue($referenceVpids);
                }
            }
        }

        return $entity;
    }

    public function restoreForeignKeys($entityName, $entity)
    {
        $entityInfo = $this->schemaInfo->getEntityInfo($entityName);

        foreach ($entityInfo->references as $referenceName => $targetEntity) {
            $referenceField = "vp_{$referenceName}";
            if (isset($entity[$referenceField])) {
                if ($this->isNullReference($entity[$referenceField])) {
                    $referencedId = 0;
                } else {
                    $referencedId = $this->restoreIdsInString($entity[$referenceField]);
                }

                if (!Strings::contains($referencedId, self::UNKNOWN_VPID_MARK)) {
                    $entity[$referenceName] = $referencedId;
                    unset($entity[$referenceField]);
                }
            }
        }

        foreach ($entityInfo->valueReferences as $referenceName => $targetEntity) {
            list($sourceColumn, $sourceValue, $valueColumn, $pathInStructure) =
                array_values(ReferenceUtils::getValueReferenceDetails($referenceName));

            if (isset($entity[$sourceColumn]) && ($entity[$sourceColumn] === $sourceValue ||
                    ReferenceUtils::valueMatchesWildcard($sourceValue, $entity[$sourceColumn])) &&
                isset($entity[$valueColumn])
            ) {
                if ($this->isNullReference($entity[$valueColumn])) {
                    continue;
                }

                if ($pathInStructure) {
                    $paths = ReferenceUtils::getMatchingPathsInSerializedData($entity[$valueColumn], $pathInStructure);
                } else {
                    $paths = [[]]; // root = the value itself
                }

                /** @var SerializedDataCursor[] $cursors */
                $cursors = array_map(function ($path) use (&$entity, $valueColumn) {
                    return new SerializedDataCursor($entity[$valueColumn], $path);
                }, $paths);

                foreach ($cursors as $cursor) {
                    $vpids = $cursor->getValue();
                    $referenceVpId = $this->restoreIdsInString($vpids);
                    $cursor->setValue($referenceVpId);
                }
            }
        }

        return $entity;
    }

    public function identifyEntity($entityName, $data, $id)
    {
        if ($this->schemaInfo->getEntityInfo($entityName)->usesGeneratedVpids) {
            $data['vp_id'] = IdUtil::newId();
            $this->saveId($entityName, $id, $data['vp_id']);


            $data[$this->schemaInfo->getEntityInfo($entityName)->idColumnName] = $id;
        }
        $data = $this->fillId($entityName, $data, $id);

        return $data;
    }

    public function deleteId($entityName, $id)
    {
        $tableName = $this->schemaInfo->getTableName($entityName);
        $deleteQuery = "DELETE FROM {$this->database->vp_id} WHERE `table` = \"$tableName\" AND id = '$id'";
        $this->database->query($deleteQuery);
    }

    private function saveId($entityName, $id, $vpId)
    {
        $tableName = $this->schemaInfo->getTableName($entityName);
        $query = "INSERT INTO {$this->database->vp_id} (`vp_id`, `table`, `id`)
                  VALUES (UNHEX('$vpId'), \"$tableName\", $id)";
        $this->database->query($query);
    }

    private function fillId($entityName, $data, $id)
    {
        $idColumnName = $this->schemaInfo->getEntityInfo($entityName)->idColumnName;
        if (!isset($data[$idColumnName])) {
            $data[$idColumnName] = $id;
        }
        return $data;
    }

    private function isNullReference($id)
    {
        // WordPress / plugins sometimes use empty string, zero or negative number to express null reference.
        return (is_numeric($id) && intval($id) <= 0) || $id === '';
    }

    private function replaceIdsInString($targetEntity, $stringWithIds)
    {
        return preg_replace_callback('/(\d+)/', function ($match) use ($targetEntity) {
            return $this->getVpidForEntity($targetEntity, $match[0]) ?: $match[0];
        }, $stringWithIds);
    }

    private function restoreIdsInString($stringWithVpids)
    {
        $stringWithIds = preg_replace_callback(IdUtil::getRegexMatchingId(), function ($match) {
            return $this->getIdForVpid($match[0]) ?: self::UNKNOWN_VPID_MARK;
        }, $stringWithVpids);

        return is_numeric($stringWithIds) ? intval($stringWithIds) : $stringWithIds;
    }

    /**
     * Function used in schema.yml.
     * Maps menu item with given postmeta (_menu_item_object_id) to target entity (post/category/custom url).
     *
     * @param $postmeta
     * @return null|string
     */
    public static function getMenuReference($postmeta)
    {
        global $versionPressContainer;
        /** @var Database $database */
        $database = $versionPressContainer->resolve(VersionPressServices::DATABASE);

        $menuItemType = $database->get_var("select meta_value from {$database->postmeta} pm join {$database->vp_id} vpid
                                            on pm.post_id = vpid.id where pm.meta_key = '_menu_item_type'
                                            and vpid.vp_id = UNHEX(\"{$postmeta['vp_post_id']}\")");

        if ($menuItemType === 'taxonomy') {
            return 'terms';
        }

        if ($menuItemType === 'post_type') {
            return 'post';
        }

        // Special case - reference to homepage (WP sets it as 'custom', but actually it is 'post_type')
        if ($menuItemType === 'custom' && is_numeric($postmeta['meta_value'])) {
            return 'post';
        }

        return null; // custom url or unknown target
    }
}
