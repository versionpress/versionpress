<?php

/**
 * Initializes ("activates" in UI terms) VersionPress - builds its internal repository and starts tracking the changes.
 *
 * Tip: to quickly test "undo" initialization for rapid testing, use `wp vp-automate start-over` command,
 * see VpAutomateCommand.
 *
 * @see VpAutomateCommand::startOver
 */
class Initializer {

    /**
     * Array of functions to call when the progress changes. Implements part of the Observer pattern.
     *
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
     * @var StorageFactory
     */
    private $storageFactory;

    /**
     * @var bool
     */
    private $isDatabaseLocked;

    /**
     * @var GitRepository
     */
    private $repository;

    private $idCache;

    function __construct(wpdb $wpdb, DbSchemaInfo $dbSchema, StorageFactory $storageFactory, GitRepository $repository) {
        $this->database = $wpdb;
        $this->dbSchema = $dbSchema;
        $this->storageFactory = $storageFactory;
        $this->repository = $repository;
    }

    /**
     * Main entry point
     */
    public function initializeVersionPress() {
        $this->reportProgressChange(InitializerStates::START);
        $this->createVersionPressTables();
        $this->lockDatabase();
        $this->createVpids();
        $this->createVpidReferences();
        $this->saveDatabaseToStorages();
        $this->commitDatabase();
        $this->createGitRepository();
        $this->activateVersionPress();
        $this->doInitializationCommit();
        $this->reportProgressChange(InitializerStates::FINISHED);
    }

    private function createVersionPressTables() {
        $table_prefix = $this->database->prefix;
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

        $this->reportProgressChange(InitializerStates::DB_TABLES_CREATED);
    }

    private function lockDatabase() {
        return; // disabled for testing
        $entityNames = $this->dbSchema->getAllEntityNames();
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

    /**
     * Creates VPIDs for all entities that have a WordPress ID and stores them into `vp_id` table.
     */
    private function createVpids() {

        $entityNames = $this->dbSchema->getAllEntityNames();

        foreach ($entityNames as $entityName) {
            $this->createVpidsForEntitiesOfType($entityName);
            $this->reportProgressChange("Created identifiers for " . $entityName);
        }

        $this->reportProgressChange(InitializerStates::VPIDS_CREATED);
    }

    /**
     * If entity type identified by $entityName defines an ID column, creates a mapping between WordPress ID and VPID
     * for all entities (db rows) of such type.
     *
     * @param string $entityName E.g., "posts"
     */
    private function createVpidsForEntitiesOfType($entityName) {

        if (!$this->dbSchema->getEntityInfo($entityName)->usesGeneratedVpids) {
            return;
        }

        $idColumnName = $this->dbSchema->getEntityInfo($entityName)->idColumnName;
        $tableName = $this->getTableName($entityName);
        $entityIds = $this->database->get_col("SELECT $idColumnName FROM $tableName");

        foreach ($entityIds as $entityId) {
            $vpId = IdUtil::newId();
            $query = "INSERT INTO {$this->getTableName('vp_id')} (`table`, id, vp_id) VALUES (\"$entityName\", $entityId, UNHEX('$vpId'))";
            $this->database->query($query);
            $this->idCache[$entityName][$entityId] = $vpId;
        }
    }

    /**
     * Creates mappings between VPIDs for all applicable entities (those that have VPIDs and reference
     * other entitites with VPIDs). Stores those mappings into the `vp_references` table.
     */
    private function createVpidReferences() {

        $entityNames = $this->dbSchema->getAllEntityNames();

        foreach ($entityNames as $entityName) {
            $this->createVpidReferencesForEntitiesOfType($entityName);
            $this->reportProgressChange("Saved references for " . $entityName);
        }

        $this->reportProgressChange(InitializerStates::REFERENCES_CREATED);

    }

    /**
     * If entity type identified by $entityName has references to other entity types, it creates a mapping
     * between VPIDs for all the entities of the current type.
     *
     * @param string $entityName E.g., "posts"
     */
    private function createVpidReferencesForEntitiesOfType($entityName) {

        if (!$this->dbSchema->getEntityInfo($entityName)->hasReferences) {
            return;
        }

        $references = $this->dbSchema->getEntityInfo($entityName)->references;

        $idColumnName = $this->dbSchema->getEntityInfo($entityName)->idColumnName;
        $referenceNames = array_keys($references);
        $entities = $this->database->get_results("SELECT $idColumnName, " . join(", ", $referenceNames) . " FROM {$this->getTableName($entityName)}", ARRAY_A);

        foreach ($entities as $entity) {
            foreach ($references as $referenceName => $targetEntity) {
                if ($entity[$referenceName] == 0)
                    continue;

                $vpId = $this->idCache[$entityName][$entity[$idColumnName]];
                $referenceVpId = $this->idCache[$targetEntity][$entity[$referenceName]];

                $query = "INSERT INTO {$this->getTableName('vp_references')} (`table`, reference, vp_id, reference_vp_id) " .
                    "VALUES (\"$entityName\", \"$referenceName\", UNHEX('$vpId'), UNHEX('$referenceVpId'))";
                $this->database->query($query);
            }
        }
    }

    /**
     * Saves all eligible entities into the file system storage (the 'db' folder)
     */
    private function saveDatabaseToStorages() {

        FileSystem::mkdir(VERSIONPRESS_MIRRORING_DIR);

        $storageNames = $this->storageFactory->getAllSupportedStorages();
        foreach ($storageNames as $entityName) {
            $this->saveEntitiesOfTypeToStorage($entityName);
            $this->reportProgressChange("All " . $entityName . " saved into files");
        }
    }

    /**
     * Saves entities of type identified by $entityName to their appropriate storage
     * (chosen by factory).
     *
     * @param string $entityName
     */
    private function saveEntitiesOfTypeToStorage($entityName) {
        $storage = $this->storageFactory->getStorage($entityName);
        $entities = $this->database->get_results("SELECT * FROM {$this->getTableName($entityName)}", ARRAY_A);
        $entities = array_filter($entities, function ($entity) use ($storage) {
            return $storage->shouldBeSaved($entity);
        });
        $entities = $this->extendEntitiesWithVpids($entityName, $entities);
        $entities = $this->replaceForeignKeysWithReferencesInAllEntities($entityName, $entities);
        $entities = $this->doEntitySpecificActions($entityName, $entities);
        $storage->prepareStorage();
        $storage->saveAll($entities);
    }

    private function replaceForeignKeysWithReferencesInAllEntities($entityName, $entities) {
        if (!$this->dbSchema->getEntityInfo($entityName)->hasReferences) {
            return $entities;
        }

        $_this = $this;
        return array_map(function ($entity) use ($entityName, $_this) {
            return $_this->replaceForeignKeysWithReferences($entityName, $entity);
        }, $entities);
    }

    public function replaceForeignKeysWithReferences($entityName, $entity) {
        $references = $this->dbSchema->getEntityInfo($entityName)->references;
        foreach ($references as $referenceName => $targetEntity) {

            if ($entity[$referenceName] > 0) {
                $referenceId = $this->idCache[$targetEntity][$entity[$referenceName]];
                $entity['vp_' . $referenceName] = $referenceId;
            }

            unset($entity[$referenceName]);
        }

        return $entity;
    }

    private function extendEntitiesWithVpids($entityName, $entities) {
        if (!$this->dbSchema->getEntityInfo($entityName)->usesGeneratedVpids) {
            return $entities;
        }

        $idColumnName = $this->dbSchema->getEntityInfo($entityName)->idColumnName;
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
        $idColumnName = $this->dbSchema->getEntityInfo('posts')->idColumnName;
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


    /**
     * Rolls back database if it was locked by `lockDatase()` and an unexpected shutdown occurred.
     */
    private function rollbackDatabase() {
        if ($this->isDatabaseLocked) {
            $this->database->query("ROLLBACK");
            $this->database->query("UNLOCK TABLES");
            $this->isDatabaseLocked = false;
        }
    }

    /**
     * Commits db changes if database has been locked
     */
    private function commitDatabase() {
        if ($this->isDatabaseLocked) {
            $this->database->query("COMMIT");
            $this->database->query("UNLOCK TABLES");
            $this->isDatabaseLocked = false;
        }

        $this->reportProgressChange(InitializerStates::DB_WORK_DONE);
    }


    private function createGitRepository() {
        if (!$this->repository->isVersioned()) {
            $this->reportProgressChange(InitializerStates::CREATING_GIT_REPOSITORY);
            $this->repository->init();
        }

        $this->repository->assumeUnchanged('wp-config.php');
    }


    private function activateVersionPress() {
        copy(VERSIONPRESS_PLUGIN_DIR . '/_db.php', WP_CONTENT_DIR . '/db.php');
        touch(VERSIONPRESS_ACTIVATION_FILE);
        file_put_contents(dirname(VERSIONPRESS_ACTIVATION_FILE) . '/.gitignore', basename(VERSIONPRESS_ACTIVATION_FILE));
        $this->reportProgressChange(InitializerStates::VERSIONPRESS_ACTIVATED);
    }


    private function doInitializationCommit() {
        $this->reportProgressChange(InitializerStates::CREATING_INITIAL_COMMIT);
        $installationChangeInfo = new VersionPressChangeInfo();

        $currentUser = wp_get_current_user();
        $authorName = $currentUser->display_name;
        $authorEmail = $currentUser->user_email;

        if (defined('WP_CLI') && WP_CLI) {
            $authorName = "wp-cli";
            $authorEmail = "wp-cli@example.com";
        }

        $this->repository->add("*");
        $this->repository->commit($installationChangeInfo->getCommitMessage(), $authorName, $authorEmail);
        $lastCommitHash = $this->repository->getLastCommitHash();
        file_put_contents(VERSIONPRESS_ACTIVATION_FILE, $lastCommitHash);
    }




    //----------------------------------------
    // Helper functions
    //----------------------------------------

    /**
     * Calls the registered `onProgressChanged` functions with the progress $message
     *
     * @param string $message
     */
    private function reportProgressChange($message) {
        foreach ($this->onProgressChanged as $listener) {
            call_user_func($listener, $message);
        }
    }

    /**
     * Could as well be a call to $dbSchema->getPrefixedTableName(). However, constructing
     * prefixed table name is found in multiple locations in the project currently so
     * hopefully this will be refactored at once some time in the future.
     *
     * @param $entityName
     * @return string
     */
    private function getTableName($entityName) {
        return $this->database->prefix . $entityName;
    }

}