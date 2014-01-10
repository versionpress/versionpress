<?php

require_once(dirname(__FILE__) . '/../../../wp-load.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/storages/EntityStorage.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/storages/ObservableStorage.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/storages/DirectoryStorage.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/storages/EntityStorageFactory.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/storages/CommentStorage.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/storages/PostStorage.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/storages/SingleFileStorage.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/storages/OptionsStorage.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/storages/TermsStorage.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/storages/TermTaxonomyStorage.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/storages/UserStorage.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/storages/UserMetaStorage.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/database/DbSchemaInfo.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/database/ExtendedWpdb.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/database/MirroringDatabase.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/utils/IniSerializer.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/utils/Git.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/utils/Neon.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/utils/Uuid.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/Mirror.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/ChangeInfo.php');


class VersionPressInstaller {
    /**
     * @var callable[]
     */
    public $onProgressChanged = array();
    /**
     * @var wpdb
     */
    private $database;
    /**
     * @var DbSchemaInfo
     */
    private $dbSchema;
    /**
     * @var EntityStorageFactory
     */
    private $storageFactory;
    private $tablePrefix;
    private $isDatabaseLocked;
    private $idCache;

    function __construct(wpdb $wpdb, DbSchemaInfo $dbSchema, EntityStorageFactory $storageFactory, $tablePrefix) {
        $this->database = $wpdb;
        $this->dbSchema = $dbSchema;
        $this->storageFactory = $storageFactory;
        $this->tablePrefix = $tablePrefix;
    }

    public function install() {
        $this->reportProgressChange("Installation starts");
        $this->createVersionPressTables();
        $this->lockDatabase();
        $this->createIdentifiers();
        $this->saveReferences();
        $this->saveDatabaseToStorages();
        $this->commitDatabase();
        $this->createGitRepository();
        $this->reportProgressChange("Installation finished");
    }

    private function createVersionPressTables() {
        $table_prefix = $this->tablePrefix;
        $process = array();

        $process[] = "DROP VIEW IF EXISTS `{$table_prefix}vp_reference_details`";
        $process[] = "DROP TABLE IF EXISTS `{$table_prefix}vp_references`";
        $process[] = "DROP TABLE IF EXISTS `{$table_prefix}vp_id`";
        $process[] = "CREATE TABLE `{$table_prefix}vp_id` (
          `vp_id` BINARY(16) NOT NULL,
          `table` VARCHAR(64) NOT NULL,
          `id` BIGINT(20) NOT NULL,
          PRIMARY KEY (`vp_id`),
          UNIQUE KEY `table_id` (`table`,`id`),
          KEY `id` (`id`)
        ) ENGINE=InnoDB;";

        $process[] = "CREATE TABLE `{$table_prefix}vp_references` (
          `table` VARCHAR(64) NOT NULL,
          `reference` VARCHAR(64) NOT NULL,
          `vp_id` BINARY(16) NOT NULL,
          `reference_vp_id` BINARY(16) NOT NULL,
          PRIMARY KEY (`table`,`reference`,`vp_id`),
          KEY `reference_vp_id` (`reference_vp_id`),
          KEY `vp_id` (`vp_id`),
          CONSTRAINT `ref_vp_id` FOREIGN KEY (`vp_id`) REFERENCES `{$table_prefix}vp_id` (`vp_id`) ON DELETE CASCADE ON UPDATE CASCADE,
          CONSTRAINT `ref_reference_vp_id` FOREIGN KEY (`reference_vp_id`) REFERENCES `{$table_prefix}vp_id` (`vp_id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB;";

        $process[] = "CREATE VIEW `{$table_prefix}vp_reference_details` AS
          SELECT `vp_id`.*, `vp_ref`.`reference`, `vp_ref`.`reference_vp_id`, `vp_id_ref`.`id` `reference_id`
          FROM `{$table_prefix}vp_id` `vp_id`
          JOIN `{$table_prefix}vp_references` `vp_ref` ON `vp_id`.`vp_id` = `vp_ref`.`vp_id`
          JOIN `{$table_prefix}vp_id` `vp_id_ref` ON `vp_ref`.`reference_vp_id` = `vp_id_ref`.`vp_id`;";

        foreach ($process as $query) {
            $this->database->query($query);
        }

        $this->reportProgressChange("Created DB tables");
    }

    private function lockDatabase() {
        return; // disabled for testing
        $entityNames = $this->dbSchema->getEntityNames();
        $tablePrefix = $this->tablePrefix;
        $tableNames = array_map(function ($entityName) use ($tablePrefix) {
            return "`" . $tablePrefix . $entityName . "`";
        }, $entityNames);

        $lockQueries = array();
        $lockQueries[] = "FLUSH TABLES " . join(",", $tableNames) . " WITH READ LOCK;";
        $lockQueries[] = "SET AUTOCOMMIT=0;";
        $lockQueries[] = "START TRANSACTION;";

        register_shutdown_function(array($this, 'rollbackDatabase'));

        foreach ($lockQueries as $lockQuery)
            $this->database->query($lockQuery);

        $this->isDatabaseLocked = true;
    }

    private function createIdentifiers() {
        $entityNames = $this->dbSchema->getEntityNames();
        foreach ($entityNames as $entityName) {
            $this->createIdentifiersForEntityType($entityName);
            $this->reportProgressChange("Created identifiers for " . $entityName);
        }
    }

    private function createIdentifiersForEntityType($entityName) {
        if (!$this->dbSchema->hasId($entityName))
            return;

        $idColumnName = $this->dbSchema->getIdColumnName($entityName);
        $tableName = $this->getTableName($entityName);
        $entityIds = $this->database->get_col("SELECT $idColumnName FROM $tableName");

        foreach ($entityIds as $entityId) {
            $vpId = Uuid::newUuidWithoutDelimiters();
            $query = "INSERT INTO {$this->getTableName('vp_id')} (`table`, id, vp_id) VALUES (\"$entityName\", $entityId, UNHEX('$vpId'))";
            $this->database->query($query);
            $this->idCache[$entityName][$entityId] = $vpId;
        }
    }

    private function saveReferences() {
        $entityNames = $this->dbSchema->getEntityNames();
        foreach ($entityNames as $entityName) {
            $this->saveReferencesForEntityType($entityName);
            $this->reportProgressChange("Saved references to " . $entityName);
        }
    }

    private function saveReferencesForEntityType($entityName) {
        if (!$this->dbSchema->hasReferences($entityName))
            return;

        $references = $this->dbSchema->getReferences($entityName);

        $idColumnName = $this->dbSchema->getIdColumnName($entityName);
        $referenceNames = array_keys($references);
        $entities = $this->database->get_results("SELECT $idColumnName, " . join(", ", $referenceNames) . " FROM {$this->getTableName($entityName)}", ARRAY_A);

        foreach ($entities as $entity) {
            foreach ($references as $referenceName => $referenceInfo) {
                if ($entity[$referenceName] == 0)
                    continue;

                $vpId = $this->idCache[$entityName][$entity[$idColumnName]];
                $targetEntity = $referenceInfo['table'];
                $referenceVpId = $this->idCache[$targetEntity][$entity[$referenceName]];

                $query = "INSERT INTO {$this->getTableName('vp_references')} (`table`, reference, vp_id, reference_vp_id) " .
                    "VALUES (\"$entityName\", \"$referenceName\", UNHEX('$vpId'), UNHEX('$referenceVpId'))";
                $this->database->query($query);
            }
        }
    }

    private function rollbackDatabase() {
        if ($this->isDatabaseLocked) {
            $this->database->query("ROLLBACK");
            $this->database->query("UNLOCK TABLES");
            $this->isDatabaseLocked = false;
        }
    }

    private function commitDatabase() {
        if ($this->isDatabaseLocked) {
            $this->database->query("COMMIT");
            $this->database->query("UNLOCK TABLES");
            $this->isDatabaseLocked = false;
        }
        $this->reportProgressChange("Finishing changes");
    }

    private function getTableName($entityName) {
        return $this->tablePrefix . $entityName;
    }

    private function saveDatabaseToStorages() {
        $storageNames = $this->storageFactory->getAllSupportedStorages();
        foreach ($storageNames as $entityName) {
            $this->saveAllEntitiesToStorage($entityName);
            $this->reportProgressChange("All " . $entityName . "saved into files");
        }
    }

    private function saveAllEntitiesToStorage($entityName) {
        $storage = $this->storageFactory->getStorage($entityName);
        $entities = $this->database->get_results("SELECT * FROM {$this->getTableName($entityName)}", ARRAY_A);
        $entities = array_filter($entities, function ($entity) use ($storage) {
            return $storage->shouldBeSaved($entity);
        });
        $entities = $this->extendEntitiesWithIdentifiers($entityName, $entities);
        $entities = $this->replaceForeignKeysWithReferencesInAllEntities($entityName, $entities);
        $entities = $this->doEntitySpecificActions($entityName, $entities);
        $storage->prepareStorage();
        $storage->saveAll($entities);
    }

    private function replaceForeignKeysWithReferencesInAllEntities($entityName, $entities) {
        if (!$this->dbSchema->hasReferences($entityName))
            return $entities;

        $_this = $this;
        return array_map(function ($entity) use ($entityName, $_this) {
            return $_this->replaceForeignKeysWithReferences($entityName, $entity);
        }, $entities);
    }

    public function replaceForeignKeysWithReferences($entityName, $entity) {
        $references = $this->dbSchema->getReferences($entityName);
        foreach ($references as $referenceName => $referenceInfo) {
            $targetEntity = $referenceInfo['table'];

            if ($entity[$referenceName] > 0) {
                $referenceId = $this->idCache[$targetEntity][$entity[$referenceName]];
                $entity['vp_' . $referenceName] = $referenceId;
            }

            unset($entity[$referenceName]);
        }

        return $entity;
    }

    private function extendEntitiesWithIdentifiers($entityName, $entities) {
        if (!$this->dbSchema->hasId($entityName))
            return $entities;

        $idColumnName = $this->dbSchema->getIdColumnName($entityName);
        $idCache = $this->idCache;

        $entities = array_map(function ($entity) use ($entityName, $idColumnName, $idCache) {
            $entity['vp_id'] = $idCache[$entityName][intval($entity[$idColumnName])];
            return $entity;
        }, $entities);

        return $entities;
    }

    private function doEntitySpecificActions($entityName, $entities) {
        if ($entityName === 'posts') {
            return array_map(array($this, 'extendPostWithTaxonomies'), $entities);
        }
        if ($entityName === 'usermeta') {
            return array_map(array($this, 'restoreUserIdInUsermeta'), $entities);
        }
        return $entities;
    }

    private function extendPostWithTaxonomies($post) {
        $idColumnName = $this->dbSchema->getIdColumnName('posts');
        $id = $post[$idColumnName];

        $postType = $post['post_type'];
        $taxonomies = get_object_taxonomies($postType);


        foreach ($taxonomies as $taxonomy) {
            $terms = get_the_terms($id, $taxonomy);
            if ($terms) {
                $idCache = $this->idCache;
                $post[$taxonomy] = array_map(function ($term) use ($idCache) {
                    return $idCache['terms'][$term->term_id];
                }, $terms);
            }
        }

        return $post;
    }

    private function restoreUserIdInUsermeta($usermeta) {
        $userIds = $this->idCache['users'];
        foreach ($userIds as $userId => $vpId) {
            if (strval($vpId) === strval($usermeta['vp_user_id'])) {
                $usermeta['user_id'] = $userId;
                return $usermeta;
            }
        }

        return $usermeta;
    }

    private function createGitRepository() {
        if(!Git::isVersioned(dirname(__FILE__))) {
            $this->reportProgressChange("Creating Git repository...");
            Git::createGitRepository(ABSPATH);
            $this->reportProgressChange("Repository created");
        }

        Git::assumeUnchanged('wp-config.php');
        Git::commit('Installed VersionPress');
    }

    private function reportProgressChange($message) {
        foreach($this->onProgressChanged as $listener) {
            call_user_func($listener, $message);
        }
    }
}