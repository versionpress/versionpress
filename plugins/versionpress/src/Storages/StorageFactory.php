<?php

namespace VersionPress\Storages;

use Nette\Utils\Strings;
use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;

class StorageFactory
{

    private $vpdbDir;
    private $dbSchemaInfo;

    private $storageClassInfo = [];

    private $storages = [];
    const ENTITY_INFO = '%entityInfo%';
    /** @var Database */
    private $database;
    private $taxonomies;

    /**
     * @param string $vpdbDir Path to the `wp-content/vpdb` directory
     * @param DbSchemaInfo $dbSchemaInfo Passed to storages
     * @param Database $database
     * @param string[] $taxonomies List of taxonomies used on current site
     */
    public function __construct($vpdbDir, DbSchemaInfo $dbSchemaInfo, $database, $taxonomies)
    {
        $this->vpdbDir = $vpdbDir;
        $this->dbSchemaInfo = $dbSchemaInfo;
        $this->database = $database;
        $this->taxonomies = $taxonomies;
        $this->initStorageClasses();
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

        $storageClass = $this->getStorageClass($entityName);
        if (class_exists($storageClass)) {
            $rc = new \ReflectionClass($storageClass);
            $args = $this->getStorageArgs($entityName);
            $storage = $rc->newInstanceArgs($args);
            $this->storages[$entityName] = $storage;
            return $storage;
        }
        return null;
    }

    public function getAllSupportedStorages()
    {
        return array_keys($this->storageClassInfo);
    }

    private function initStorageClasses()
    {
        $this->addStorageClassInfo('post', PostStorage::class, '%vpdb%/posts', self::ENTITY_INFO);
        $this->addStorageClassInfo(
            'comment',
            CommentStorage::class,
            '%vpdb%/comments',
            self::ENTITY_INFO,
            '%database%'
        );
        $this->addStorageClassInfo(
            'option',
            OptionStorage::class,
            '%vpdb%/options',
            self::ENTITY_INFO,
            $this->database->prefix,
            $this->taxonomies
        );
        $this->addStorageClassInfo('term', TermStorage::class, '%vpdb%/terms', self::ENTITY_INFO);
        $this->addStorageClassInfo(
            'term_taxonomy',
            TermTaxonomyStorage::class,
            '%vpdb%/term_taxonomies',
            self::ENTITY_INFO,
            '%storage(term)%'
        );
        $this->addStorageClassInfo('user', UserStorage::class, '%vpdb%/users', self::ENTITY_INFO);
        $this->addStorageClassInfo(
            'usermeta',
            UserMetaStorage::class,
            '%storage(user)%',
            self::ENTITY_INFO,
            $this->database->prefix
        );
        $this->addStorageClassInfo('postmeta', PostMetaStorage::class, '%storage(post)%', self::ENTITY_INFO);
        $this->addStorageClassInfo('termmeta', TermMetaStorage::class, '%storage(term)%', self::ENTITY_INFO);
        $this->addStorageClassInfo('commentmeta', CommentMetaStorage::class, '%storage(comment)%', self::ENTITY_INFO);
    }

    private function addStorageClassInfo($entityName, $className, $args)
    {
        $args = func_get_args();
        array_shift($args); // remove $entityName
        array_shift($args); // remove $className

        $this->storageClassInfo[$entityName] = [
            'class' => $className,
            'args' => $args
        ];
    }

    private function getStorageClass($entityName)
    {
        if (!isset($this->storageClassInfo[$entityName])) {
            return null;
        }
        return $this->storageClassInfo[$entityName]['class'];
    }

    private function getStorageArgs($entityName)
    {
        $args = $this->storageClassInfo[$entityName]['args'];
        return $this->expandArgs($entityName, $args);
    }

    private function expandArgs($entityName, $args)
    {
        $expandedArgs = [];
        foreach ($args as $arg) {
            if (is_string($arg) && Strings::contains($arg, '%vpdb%')) {
                $arg = str_replace('%vpdb%', $this->vpdbDir, $arg);
            }

            if (is_string($arg) && $arg === self::ENTITY_INFO) {
                $arg = $this->dbSchemaInfo->getEntityInfo($entityName);
            }

            if (is_string($arg) && Strings::contains($arg, '%storage')) {
                $matches = Strings::match($arg, '%storage\((.*)\)%');
                $entity = $matches[1];
                $arg = $this->getStorage($entity);
            }

            if (is_string($arg) && $arg === '%database%') {
                $arg = $this->database;
            }

            $expandedArgs[] = $arg;
        }
        return $expandedArgs;
    }
}
