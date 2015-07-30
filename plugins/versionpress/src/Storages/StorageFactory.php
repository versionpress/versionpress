<?php

namespace VersionPress\Storages;

use Nette\Utils\Strings;
use Tracy\Debugger;
use VersionPress\Database\DbSchemaInfo;

class StorageFactory {

    private $vpdbDir;
    private $dbSchemaInfo;

    private $storageClassInfo = array();

    private $storages = array();
    const ENTITY_INFO = '%entityInfo%';
    /** @var \wpdb */
    private $database;

    /**
     * @param string $vpdbDir Path to the `wp-content/vpdb` directory
     * @param DbSchemaInfo $dbSchemaInfo Passed to storages
     * @param \wpdb $wpdb
     */
    function __construct($vpdbDir, DbSchemaInfo $dbSchemaInfo, $wpdb) {
        $this->vpdbDir = $vpdbDir;
        $this->dbSchemaInfo = $dbSchemaInfo;
        $this->database = $wpdb;
        $this->initStorageClasses();
    }

    /**
     * Returns storage by given entity type
     *
     * @param string $entityName
     * @return Storage|null
     */
    public function getStorage($entityName) {
        if (isset($this->storages[$entityName]))
            return $this->storages[$entityName];

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

    public function getAllSupportedStorages() {
        return array_keys($this->storageClassInfo);
    }

    private function initStorageClasses() {
        $this->addStorageClassInfo('post', 'VersionPress\Storages\PostStorage', '%vpdb%/posts', self::ENTITY_INFO);
        $this->addStorageClassInfo('comment', 'VersionPress\Storages\CommentStorage', '%vpdb%/comments', self::ENTITY_INFO, '%database%');
        $this->addStorageClassInfo('option', 'VersionPress\Storages\OptionsStorage', '%vpdb%/options.ini', self::ENTITY_INFO, $this->database->prefix);
        $this->addStorageClassInfo('term', 'VersionPress\Storages\TermsStorage', '%vpdb%/terms.ini', self::ENTITY_INFO);
        $this->addStorageClassInfo('term_taxonomy', 'VersionPress\Storages\TermTaxonomyStorage', '%vpdb%/terms.ini', self::ENTITY_INFO);
        $this->addStorageClassInfo('user', 'VersionPress\Storages\UserStorage', '%vpdb%/users', self::ENTITY_INFO);
        $this->addStorageClassInfo('usermeta', 'VersionPress\Storages\UserMetaStorage', '%storage(user)%', $this->database->prefix);
        $this->addStorageClassInfo('postmeta', 'VersionPress\Storages\PostMetaStorage', '%storage(post)%');
    }

    private function addStorageClassInfo($entityName, $className, $args) {
        $args = func_get_args();
        array_shift($args); // remove $entityName
        array_shift($args); // remove $className

        $this->storageClassInfo[$entityName] = array(
            'class' => $className,
            'args' => $args
        );
    }

    private function getStorageClass($entityName) {
        if (!isset($this->storageClassInfo[$entityName])) {
            return null;
        }
        return $this->storageClassInfo[$entityName]['class'];
    }

    private function getStorageArgs($entityName) {
        $args = $this->storageClassInfo[$entityName]['args'];
        return $this->expandArgs($entityName, $args);
    }

    private function expandArgs($entityName, $args) {
        $expandedArgs = array();
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