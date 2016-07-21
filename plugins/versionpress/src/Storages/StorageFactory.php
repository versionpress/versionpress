<?php

namespace VersionPress\Storages;

use Nette\Utils\Strings;
use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Git\ActionsInfo;

class StorageFactory
{

    private $vpdbDir;
    private $dbSchemaInfo;

    private $storages = [];
    /** @var Database */
    private $database;
    private $taxonomies;
    /** @var ActionsInfo */
    private $actionsInfo;

    /**
     * @param string $vpdbDir Path to the `wp-content/vpdb` directory
     * @param DbSchemaInfo $dbSchemaInfo Passed to storages
     * @param Database $database
     * @param string[] $taxonomies List of taxonomies used on current site
     * @param ActionsInfo $actionsInfo
     */
    public function __construct($vpdbDir, DbSchemaInfo $dbSchemaInfo, $database, $taxonomies, $actionsInfo)
    {
        $this->vpdbDir = $vpdbDir;
        $this->dbSchemaInfo = $dbSchemaInfo;
        $this->database = $database;
        $this->taxonomies = $taxonomies;
        $this->actionsInfo = $actionsInfo;
    }

    /**
     * Returns storage by given entity type
     *
     * @param string $entityName
     * @return Storage|null
     */
    public function getStorage($entityName)
    {
        if (isset($this->storages[$entityName])) {
            return $this->storages[$entityName];
        }

        $entityInfo = $this->dbSchemaInfo->getEntityInfo($entityName);
        if (!$entityInfo) {
            return null;
        }

        if ($this->dbSchemaInfo->isChildEntity($entityName)) {
            if (isset($entityInfo->storageClass)) {
                $storageClass = $entityInfo->storageClass;
            } else {
                $storageClass = MetaEntityStorage::class;
            }

            $parentEntity = $entityInfo->references[$entityInfo->parentReference];
            $parentStorage = $this->getStorage($parentEntity);

            return new $storageClass($parentStorage, $entityInfo, $this->database->prefix, $this->actionsInfo);
        }

        if (isset($entityInfo->storageClass)) {
            $storageClass = $entityInfo->storageClass;
        } else {
            $storageClass = DirectoryStorage::class;
        }

        return new $storageClass($this->vpdbDir . '/' . $entityInfo->tableName, $entityInfo, $this->database->prefix, $this->actionsInfo);
    }

    public function getAllSupportedStorages()
    {
        return $this->dbSchemaInfo->getAllEntityNames();
    }
}
