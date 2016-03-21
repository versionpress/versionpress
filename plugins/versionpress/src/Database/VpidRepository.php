<?php

namespace VersionPress\Database;

use VersionPress\DI\VersionPressServices;
use VersionPress\Utils\IdUtil;
use VersionPress\Utils\ReferenceUtils;
use wpdb;

class VpidRepository {
    /** @var wpdb */
    private $database;
    /** @var DbSchemaInfo */
    private $schemaInfo;
    /** @var string */
    private $vpidTableName;

    public function __construct($wpdb, DbSchemaInfo $schemaInfo) {
        $this->database = $wpdb;
        $this->schemaInfo = $schemaInfo;
        $this->vpidTableName = $schemaInfo->getPrefixedTableName('vp_id');
    }

    /**
     * Returns VPID of entity of given type and id.
     *
     * @param $entityName
     * @param $id
     * @return null|string
     */
    public function getVpidForEntity($entityName, $id) {
        $tableName = $this->schemaInfo->getTableName($entityName);
        return $this->database->get_var("SELECT HEX(vp_id) FROM $this->vpidTableName WHERE id = '$id' AND `table` = '$tableName'");
    }

    public function getIdForVpid($vpid) {
        return $this->database->get_var("SELECT id FROM $this->vpidTableName WHERE vp_id = UNHEX('$vpid')");
    }

    public function replaceForeignKeysWithReferences($entityName, $entity) {
        $entityInfo = $this->schemaInfo->getEntityInfo($entityName);
        $vpIdTable = $this->schemaInfo->getPrefixedTableName('vp_id');

        foreach ($entityInfo->references as $referenceName => $targetEntity) {
            $targetTable = $this->schemaInfo->getEntityInfo($targetEntity)->tableName;

            if (isset($entity[$referenceName])) {
                if ($entity[$referenceName] > 0) {
                    $referenceVpId = $this->database->get_var("SELECT HEX(vp_id) FROM $vpIdTable WHERE `table` = '$targetTable' AND id=$entity[$referenceName]");
                } else {
                    $referenceVpId = 0;
                }

                $entity['vp_' . $referenceName] = $referenceVpId;
                unset($entity[$referenceName]);
            }

        }

        foreach ($entityInfo->valueReferences as $referenceName => $targetEntity) {
            list($sourceColumn, $sourceValue, $valueColumn) = array_values(ReferenceUtils::getValueReferenceDetails($referenceName));

            if (isset($entity[$sourceColumn]) && $entity[$sourceColumn] == $sourceValue && isset($entity[$valueColumn])) {

                if ($entity[$valueColumn] == 0) {
                    continue;
                }

                if ($targetEntity[0] === '@') {
                    $entityNameProvider = substr($targetEntity, 1);
                    $targetEntity = call_user_func($entityNameProvider, $entity);
                    if (!$targetEntity) {
                        continue;
                    }
                }
                $targetTable = $this->schemaInfo->getEntityInfo($targetEntity)->tableName;

                $referenceVpId = $this->database->get_var("SELECT HEX(vp_id) FROM $vpIdTable WHERE `table` = '$targetTable' AND id=" . $entity[$valueColumn]);
                $entity[$valueColumn] = $referenceVpId;
            }
        }

        return $entity;
    }

    public function identifyEntity($entityName, $data, $id) {
        if ($this->schemaInfo->getEntityInfo($entityName)->usesGeneratedVpids) {
            $data['vp_id'] = IdUtil::newId();
            $this->saveId($entityName, $id, $data['vp_id']);


            $data[$this->schemaInfo->getEntityInfo($entityName)->idColumnName] = $id;
        }
        $data = $this->fillId($entityName, $data, $id);

        return $data;
    }

    public function deleteId($entityName, $id) {
        $vpIdTableName = $this->schemaInfo->getPrefixedTableName('vp_id');
        $tableName = $this->schemaInfo->getTableName($entityName);
        $deleteQuery = "DELETE FROM $vpIdTableName WHERE `table` = \"$tableName\" AND id = '$id'";
        $this->database->vp_direct_query($deleteQuery);
    }

    private function saveId($entityName, $id, $vpId) {
        $vpIdTableName = $this->schemaInfo->getPrefixedTableName('vp_id');
        $tableName = $this->schemaInfo->getTableName($entityName);
        $query = "INSERT INTO $vpIdTableName (`vp_id`, `table`, `id`) VALUES (UNHEX('$vpId'), \"$tableName\", $id)";
        $this->database->vp_direct_query($query);
    }

    private function fillId($entityName, $data, $id) {
        $idColumnName = $this->schemaInfo->getEntityInfo($entityName)->idColumnName;
        if (!isset($data[$idColumnName])) {
            $data[$idColumnName] = $id;
        }
        return $data;
    }

    /**
     * Function used in wordpress-schema.yml.
     * Maps menu item with given postmeta (_menu_item_object_id) to target entity (post/category/custom url).
     *
     * @param $postmeta
     * @return null|string
     */
    public static function getMenuReference($postmeta) {
        global $versionPressContainer;
        /** @var \VersionPress\Storages\StorageFactory $storageFactory */
        $storageFactory = $versionPressContainer->resolve(VersionPressServices::STORAGE_FACTORY);
        /** @var \VersionPress\Storages\PostMetaStorage $postmetaStorage */
        $postmetaStorage = $storageFactory->getStorage('postmeta');
        $menuItemTypePostmeta = $postmetaStorage->loadEntityByName('_menu_item_type', $postmeta['vp_post_id']);
        $menuItemType = $menuItemTypePostmeta['meta_value'];

        if ($menuItemType === 'taxonomy') {
            return 'term_taxonomy';
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
