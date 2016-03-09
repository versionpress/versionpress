<?php
namespace VersionPress\Initialization;

use Symfony\Component\Process\Exception\ProcessTimedOutException;
use VersionPress\ChangeInfos\VersionPressChangeInfo;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\ShortcodesReplacer;
use VersionPress\Database\ShortcodesInfo;
use VersionPress\Database\VpidRepository;
use VersionPress\Git\GitConfig;
use VersionPress\Git\GitRepository;
use VersionPress\Git\MergeDriverInstaller;
use VersionPress\Storages\Storage;
use VersionPress\Storages\StorageFactory;
use VersionPress\Synchronizers\SynchronizerFactory;
use VersionPress\Utils\AbsoluteUrlReplacer;
use VersionPress\Utils\ArrayUtils;
use VersionPress\Utils\FileSystem;
use VersionPress\Utils\IdUtil;
use VersionPress\Utils\PathUtils;
use VersionPress\Utils\SecurityUtils;
use VersionPress\Utils\StringUtils;
use VersionPress\Utils\WordPressMissingFunctions;
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

    const TIME_FOR_ABORTION = 5;

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
     * @var SynchronizerFactory
     */
    private $synchronizerFactory;

    /**
     * @var bool
     */
    private $isDatabaseLocked;

    /**
     * @var GitRepository
     */
    private $repository;

    /**
     * @var AbsoluteUrlReplacer
     */
    private $urlReplacer;
    /**
     * @var VpidRepository
     */
    private $vpidRepository;
    /**
     * @var ShortcodesReplacer
     */
    private $shortcodesReplacer;
    private $idCache;
    private $executionStartTime;

    function __construct(
        $wpdb,
        DbSchemaInfo $dbSchema,
        StorageFactory $storageFactory,
        SynchronizerFactory $synchronizerFactory,
        GitRepository $repository,
        AbsoluteUrlReplacer $urlReplacer,
        VpidRepository $vpidRepository,
        ShortcodesReplacer $shortcodesReplacer) {

        $this->database = $wpdb;
        $this->dbSchema = $dbSchema;
        $this->storageFactory = $storageFactory;
        $this->synchronizerFactory = $synchronizerFactory;
        $this->repository = $repository;
        $this->urlReplacer = $urlReplacer;
        $this->vpidRepository = $vpidRepository;
        $this->shortcodesReplacer = $shortcodesReplacer;
        $this->executionStartTime = microtime(true);
    }

    /**
     * Main entry point
     */
    public function initializeVersionPress() {
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        @set_time_limit(0); // intentionally @ - if it's disabled we can't do anything but try the initialization
        $this->reportProgressChange(InitializerStates::START);
        vp_enable_maintenance();
        try {
            $this->createVersionPressTables();
            $this->lockDatabase();
            $this->saveDatabaseToStorages();
            $this->commitDatabase();
            $this->createGitRepository();
            $this->activateVersionPress();
            $this->copyAccessRulesFiles();
            $this->createCommonConfig();
            $this->doInitializationCommit();
            vp_disable_maintenance();
            $this->reportProgressChange(InitializerStates::FINISHED);
        } catch (InitializationAbortedException $ex) {
            $this->reportProgressChange(InitializerStates::ABORTED);
        }
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
        $entities = $this->replaceForeignKeysWithReferencesInAllEntities($entityName, $entities);

        $storage = $this->storageFactory->getStorage($entityName);
        $entities = array_filter($entities, function ($entity) use ($storage) { return $storage->shouldBeSaved($entity); });
        $chunks = array_chunk($entities, 1000);
        $this->idCache[$entityName] = array();

        foreach ($chunks as $entitiesInChunk) {
            $wordpressIds = ArrayUtils::column($entitiesInChunk, $idColumnName);
            $vpIds = array_map(array('VersionPress\Utils\IdUtil', 'newId'), $entitiesInChunk);
            $idPairs = array_combine($wordpressIds, $vpIds);
            $this->idCache[$entityName] = $this->idCache[$entityName] + $idPairs; // merge arrays with preserving keys
            $sqlValues = join(', ', ArrayUtils::map(function ($vpId, $id) use ($tableName) { return "('$tableName', $id, UNHEX('$vpId'))"; }, $idPairs));
            $query = "INSERT INTO {$this->getTableName('vp_id')} (`table`, id, vp_id) VALUES $sqlValues";
            $this->database->query($query);
            $this->checkTimeout();
        }
    }

    /**
     * Saves all eligible entities into the file system storage (the 'db' folder)
     */
    private function saveDatabaseToStorages() {

        if (is_dir(VP_VPDB_DIR)) {
            FileSystem::remove(VP_VPDB_DIR);
        }

        FileSystem::mkdir(VP_VPDB_DIR);

        $entityNames = $this->synchronizerFactory->getSynchronizationSequence();
        foreach ($entityNames as $entityName) {
            $this->createVpidsForEntitiesOfType($entityName);
            $this->saveEntitiesOfTypeToStorage($entityName);
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

        $entities = $this->getEntitiesFromDatabase($entityName);
        $entities = $this->replaceForeignKeysWithReferencesInAllEntities($entityName, $entities);
        $entities = $this->replaceShortcodesInAllEntities($entityName, $entities);

        $entities = array_values(array_filter($entities, function ($entity) use ($storage) {
            return $storage->shouldBeSaved($entity);
        }));

        $urlReplacer = $this->urlReplacer;
        $entities = $this->extendEntitiesWithVpids($entityName, $entities);
        $entities = array_map(function ($entity) use ($urlReplacer) { return $urlReplacer->replace($entity); }, $entities);
        $entities = $this->doEntitySpecificActions($entityName, $entities);
        $storage->prepareStorage();

        if (!$this->dbSchema->isChildEntity($entityName)) {
            $this->saveStandardEntities($storage, $entities);
        } else { // meta entities
            $entityInfo = $this->dbSchema->getEntityInfo($entityName);
            $parentReference = "vp_" . $entityInfo->parentReference;

            $this->saveMetaEntities($storage, $entities, $parentReference);
        }
    }

    private function saveStandardEntities(Storage $storage, $entities) {
        foreach ($entities as $entity) {
            $storage->save($entity);
            $this->checkTimeout();
        }
    }

    private function saveMetaEntities(Storage $storage, $entities, $parentReference) {
        if (count($entities) == 0) {
            return;
        }

        $lastParent = $entities[0][$parentReference];
        foreach ($entities as $entity) {
            if ($entity[$parentReference] !== $lastParent) {
                $storage->commit();
                $this->checkTimeout();
            }
            $storage->saveLater($entity);
        }
        $storage->commit();
    }

    private function replaceForeignKeysWithReferencesInAllEntities($entityName, $entities) {
        $vpidRepository = $this->vpidRepository;
        return array_map(function ($entity) use ($vpidRepository, $entityName) {
            return $vpidRepository->replaceForeignKeysWithReferences($entityName, $entity);
        }, $entities);
    }

    private function replaceShortcodesInAllEntities($entityName, $entities) {
        $shortcodesReplacer = $this->shortcodesReplacer;

        return array_map(function ($entity) use ($entityName, $shortcodesReplacer) {
            return $shortcodesReplacer->replaceShortcodesInEntity($entityName, $entity);
        }, $entities);
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

    public function extendPostWithTaxonomies($post) {
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
        }

        $this->installGitignore();
        MergeDriverInstaller::installMergeDriver(VP_PROJECT_ROOT, VERSIONPRESS_PLUGIN_DIR, VP_VPDB_DIR);
    }


    private function activateVersionPress() {
        WpdbReplacer::replaceMethods();
        touch(VERSIONPRESS_ACTIVATION_FILE);
        $this->reportProgressChange(InitializerStates::VERSIONPRESS_ACTIVATED);
    }


    private function doInitializationCommit() {
        $this->checkTimeout();

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

        try {
            $this->adjustGitProcessTimeout();
            $this->repository->stageAll();
            $this->adjustGitProcessTimeout();
            $this->repository->commit($installationChangeInfo->getCommitMessage(), $authorName, $authorEmail);
        } catch (ProcessTimedOutException $ex) {
            $this->abortInitialization();
        }
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
        SecurityUtils::protectDirectory(VP_PROJECT_ROOT . "/.git");
        SecurityUtils::protectDirectory(VP_VPDB_DIR);
    }


    /**
     * Installs Gitignore to the repository root, or does nothing if the file already exists.
     */
    private function installGitignore() {

        $gitignorePath = VP_PROJECT_ROOT . '/.gitignore';

        $vpGitignore = file_get_contents(__DIR__ . '/.gitignore.tpl');

        $gitIgnoreVariables = array(
            'wp-content' => '/' . PathUtils::getRelativePath(VP_PROJECT_ROOT, WP_CONTENT_DIR),
            'wp-plugins' => '/' . PathUtils::getRelativePath(VP_PROJECT_ROOT, WP_PLUGIN_DIR),
            'abspath' => '/' . PathUtils::getRelativePath(VP_PROJECT_ROOT, ABSPATH),
        );

        $vpGitignore = StringUtils::fillTemplateString($gitIgnoreVariables, $vpGitignore);

        if (is_file($gitignorePath)) {
            $currentGitignore = file_get_contents($gitignorePath);

            if (strpos($currentGitignore, $vpGitignore) !== false) {
                return;
            }

            file_put_contents($gitignorePath, "\n" . $vpGitignore, FILE_APPEND);

        } else {
            file_put_contents($gitignorePath, $vpGitignore);
        }
    }




    private function createCommonConfig() {
        $configPath = WordPressMissingFunctions::getWpConfigPath();
        $commonConfigName = 'wp-config.common.php';

        WpConfigSplitter::split($configPath, $commonConfigName);
    }

    private function adjustGitProcessTimeout() {
        $maxExecutionTime = intval(ini_get('max_execution_time'));

        if ($maxExecutionTime === 0) {
            $this->repository->setGitProcessTimeout(0);
            return;
        }

        $currentTime = microtime(true);
        $alreadyConsumedTime = $currentTime - $this->executionStartTime;
        $remainingTime = $maxExecutionTime - $alreadyConsumedTime;
        $this->checkTimeout();
        $processTimeout = $remainingTime - self::TIME_FOR_ABORTION;
        $this->repository->setGitProcessTimeout($processTimeout);
    }

    private function checkTimeout() {
        if ($this->timeoutIsClose()) {
            $this->abortInitialization();
        }
    }

    private function timeoutIsClose() {
        $maxExecutionTime = intval(ini_get('max_execution_time'));

        if ($maxExecutionTime === 0) {
            return false;
        }

        $executionTime = microtime(true) - $this->executionStartTime;
        $remainingTime = $maxExecutionTime - $executionTime;

        return $remainingTime <= self::TIME_FOR_ABORTION; // in seconds
    }

    private function abortInitialization() {
        touch(VERSIONPRESS_PLUGIN_DIR . '/.abort-initialization');

        if (VersionPress::isActive()) {
            @unlink(VERSIONPRESS_ACTIVATION_FILE);
        }

        vp_disable_maintenance();
        throw new InitializationAbortedException();
    }

    /**
     * @param $entityName
     * @return mixed
     */
    private function getEntitiesFromDatabase($entityName) {
        if ($this->dbSchema->isChildEntity($entityName)) {
            $entityInfo = $this->dbSchema->getEntityInfo($entityName);
            $parentReference = $entityInfo->parentReference;

            return $this->database->get_results("SELECT * FROM {$this->getTableName($entityName)} ORDER BY {$parentReference}", ARRAY_A);
        }

        return $this->database->get_results("SELECT * FROM {$this->getTableName($entityName)}", ARRAY_A);
    }

}
