<?php
namespace VersionPress\Initialization;

use VersionPress\ChangeInfos\VersionPressChangeInfo;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Git\GitConfig;
use VersionPress\Git\GitRepository;
use VersionPress\Storages\StorageFactory;
use VersionPress\Utils\FileSystem;
use VersionPress\Utils\IdUtil;
use VersionPress\Utils\SecurityUtils;
use VersionPress\VersionPress;
use wpdb;

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
     * @var \VersionPress\Storages\StorageFactory
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
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        @set_time_limit(0); // intentionally @ - if it's disabled we can't do anything but try the initialization

        $this->reportProgressChange(InitializerStates::START);
        vp_enable_maintenance();
        $this->createVersionPressTables();
        $this->lockDatabase();
        $this->createVpids();
        $this->saveDatabaseToStorages();
        $this->commitDatabase();
        $this->createGitRepository();
        $this->activateVersionPress();
        $this->copyAccessRulesFiles();
        $this->doInitializationCommit();
        vp_disable_maintenance();
        $this->reportProgressChange(InitializerStates::FINISHED);
    }

    public function createVersionPressTables() {
        $table_prefix = $this->database->prefix;
        $process = array();

        $process[] = "DROP TABLE IF EXISTS `{$table_prefix}vp_id`";
        $process[] = "CREATE TABLE `{$table_prefix}vp_id` (
          `vp_id` BINARY(16) NOT NULL,
          `table` VARCHAR(64) NOT NULL,
          `id` BIGINT(20) NOT NULL,
          PRIMARY KEY (`vp_id`),
          UNIQUE KEY `table_id` (`table`,`id`),
          KEY `id` (`id`)
        ) ENGINE=InnoDB;";

        foreach ($process as $query) {
            $this->database->query($query);
        }

        $this->reportProgressChange(InitializerStates::DB_TABLES_CREATED);
    }

    private function lockDatabase() {
        return; // disabled for testing
        /** @noinspection PhpUnreachableStatementInspection */
        $entityNames = $this->dbSchema->getAllEntityNames();
        $dbSchema = $this->dbSchema;
        $tableNames = array_map(function ($entityName) use ($dbSchema) {
            return "`{$dbSchema->getPrefixedTableName($entityName)}`";
        }, $entityNames);

        $lockQueries = array();
        $lockQueries[] = "FLUSH TABLES " . join(",", $tableNames) . " WITH READ LOCK;";
        $lockQueries[] = "SET AUTOCOMMIT=0;";
        $lockQueries[] = "START TRANSACTION;";

        register_shutdown_function(array('self', 'rollbackDatabase'));

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
     * @param string $entityName E.g., "post"
     */
    private function createVpidsForEntitiesOfType($entityName) {

        if (!$this->dbSchema->getEntityInfo($entityName)->usesGeneratedVpids) {
            return;
        }

        $idColumnName = $this->dbSchema->getEntityInfo($entityName)->idColumnName;
        $tableName = $this->dbSchema->getTableName($entityName);
        $prefixedTableName = $this->dbSchema->getPrefixedTableName($entityName);
        $entities = $this->database->get_results("SELECT * FROM $prefixedTableName", ARRAY_A);

        foreach ($entities as $entity) {
            if (!$this->storageFactory->getStorage($entityName)->shouldBeSaved($entity)) continue;
            $entityId = $entity[$idColumnName];
            $vpId = IdUtil::newId();
            $query = "INSERT INTO {$this->getTableName('vp_id')} (`table`, id, vp_id) VALUES (\"$tableName\", $entityId, UNHEX('$vpId'))";
            $this->database->query($query);
            $this->idCache[$entityName][$entityId] = $vpId;
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
        if ($entityName === 'post') {
            return array_map(array($this, 'extendPostWithTaxonomies'), $entities);
        }
        if ($entityName === 'usermeta') {
            return array_map(array($this, 'restoreUserIdInUsermeta'), $entities);
        }
        return $entities;
    }

    private function extendPostWithTaxonomies($post) {
        $idColumnName = $this->dbSchema->getEntityInfo('post')->idColumnName;
        $id = $post[$idColumnName];

        $postType = $post['post_type'];
        $taxonomies = get_object_taxonomies($postType);


        foreach ($taxonomies as $taxonomy) {
            $terms = get_the_terms($id, $taxonomy);
            if ($terms) {
                $idCache = $this->idCache;
                $referencedTaxonomies = array_map(function ($term) use ($idCache) {
                    return $idCache['term_taxonomy'][$term->term_taxonomy_id];
                }, $terms);

                $currentTaxonomies = isset($post['vp_term_taxonomy']) ? $post['vp_term_taxonomy'] : array();
                $post['vp_term_taxonomy'] = array_merge($currentTaxonomies, $referencedTaxonomies);
            }
        }

        return $post;
    }

    private function restoreUserIdInUsermeta($usermeta) {
        $userIds = $this->idCache['user'];
        foreach ($userIds as $userId => $vpId) {
            if (strval($vpId) === strval($usermeta['vp_user_id'])) {
                $usermeta['user_id'] = $userId;
                return $usermeta;
            }
        }

        return $usermeta;
    }


    /**
     * Rolls back database if it was locked by `lockDatabase()` and an unexpected shutdown occurred.
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
            $this->installGitignore();
        }
    }


    private function activateVersionPress() {
        copy(VERSIONPRESS_PLUGIN_DIR . '/_db.php', WP_CONTENT_DIR . '/db.php');
        touch(VERSIONPRESS_ACTIVATION_FILE);
        $this->reportProgressChange(InitializerStates::VERSIONPRESS_ACTIVATED);
    }


    private function doInitializationCommit() {

        // Since WP-217 the `.active` file contains not the SHA1 of the first commit that VersionPress
        // created but the one before that (which may be an empty string if VersionPress's commit
        // was the first one in the repository).
        $lastCommitHash = $this->repository->getLastCommitHash();
        file_put_contents(VERSIONPRESS_ACTIVATION_FILE, $lastCommitHash);


        $this->reportProgressChange(InitializerStates::CREATING_INITIAL_COMMIT);
        $installationChangeInfo = new VersionPressChangeInfo("activate", VersionPress::getVersion());

        $currentUser = wp_get_current_user();
        /** @noinspection PhpUndefinedFieldInspection */
        $authorName = $currentUser->display_name;
        /** @noinspection PhpUndefinedFieldInspection */
        $authorEmail = $currentUser->user_email;

        if (defined('WP_CLI') && WP_CLI) {
            $authorName = GitConfig::$wpcliUserName;
            $authorEmail = GitConfig::$wpcliUserEmail;
        }

        $this->repository->stageAll();
        $this->repository->commit($installationChangeInfo->getCommitMessage(), $authorName, $authorEmail);
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
        return $this->dbSchema->getPrefixedTableName($entityName);
    }

    /**
     * Copies the .htaccess and web.config files into the vpdb directory.
     */
    private function copyAccessRulesFiles() {
        SecurityUtils::protectDirectory(ABSPATH . "/.git");
        SecurityUtils::protectDirectory(VERSIONPRESS_MIRRORING_DIR);
    }

    /**
     * Installs Gitignore to the repository root, or does nothing if the file already exists.
     */
    private function installGitignore() {
        FileSystem::copy(__DIR__ . '/.gitignore.tpl', ABSPATH . '.gitignore', false);
    }

}
