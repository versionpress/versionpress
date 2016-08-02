<?php

namespace VersionPress\DI;

use VersionPress\ChangeInfos\ChangeInfoFactory;
use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\ShortcodesInfo;
use VersionPress\Database\ShortcodesReplacer;
use VersionPress\Database\SqlQueryParser;
use VersionPress\Database\VpidRepository;
use VersionPress\Database\WpdbMirrorBridge;
use VersionPress\Git\ActionsInfo;
use VersionPress\Git\Committer;
use VersionPress\Git\GitRepository;
use VersionPress\Git\Reverter;
use VersionPress\Initialization\Initializer;
use VersionPress\Storages\Mirror;
use VersionPress\Storages\StorageFactory;
use VersionPress\Synchronizers\SynchronizationProcess;
use VersionPress\Synchronizers\SynchronizerFactory;
use VersionPress\Utils\AbsoluteUrlReplacer;
use VersionPress\VersionPress;

class DIContainer
{
    /** @var DIContainer */
    private static $instance;
    private $providers;
    private $services;

    public function register($name, $serviceProvider)
    {
        $this->providers[$name] = $serviceProvider;
    }

    /**
     * @param $name string Service name
     * @return mixed Service instance
     */
    public function resolve($name)
    {
        if (!isset($this->services[$name])) {
            $provider = $this->providers[$name];
            $this->services[$name] = $provider();
        }
        return $this->services[$name];
    }

    /**
     * @return DIContainer
     */
    public static function getConfiguredInstance()
    {
        if (self::$instance != null) {
            return self::$instance;
        }

        self::$instance = $dic = new DIContainer();

        $dic->register(VersionPressServices::WPDB, function () {
            global $wpdb;
            return $wpdb;
        });

        $dic->register(VersionPressServices::DATABASE, function () use ($dic) {
            return new Database($dic->resolve(VersionPressServices::WPDB));
        });

        $dic->register(VersionPressServices::STORAGE_FACTORY, function () use ($dic) {
            global $wp_taxonomies;
            return new StorageFactory(
                VP_VPDB_DIR,
                $dic->resolve(VersionPressServices::DB_SCHEMA),
                $dic->resolve(VersionPressServices::DATABASE),
                array_keys((array)$wp_taxonomies),
                $dic->resolve(VersionPressServices::ACTIONS_INFO)
            );
        });

        $dic->register(VersionPressServices::MIRROR, function () use ($dic) {
            return new Mirror(
                $dic->resolve(VersionPressServices::STORAGE_FACTORY),
                $dic->resolve(VersionPressServices::URL_REPLACER)
            );
        });

        $dic->register(VersionPressServices::DB_SCHEMA, function () {
            global $table_prefix, $wp_db_version;
            return new DbSchemaInfo(
                VERSIONPRESS_PLUGIN_DIR . '/src/Database/wordpress-schema.yml',
                $table_prefix,
                $wp_db_version
            );
        });

        $dic->register(VersionPressServices::ACTIONS_INFO, function () {
            $vpActionsFile = VERSIONPRESS_PLUGIN_DIR . '/.versionpress/actions.yml';
            return new ActionsInfo([$vpActionsFile]);
        });

        $dic->register(VersionPressServices::CHANGEINFO_FACTORY, function () use ($dic) {
            return new ChangeInfoFactory(
                $dic->resolve(VersionPressServices::DB_SCHEMA),
                $dic->resolve(VersionPressServices::ACTIONS_INFO)
            );
        });

        $dic->register(VersionPressServices::WPDB_MIRROR_BRIDGE, function () use ($dic) {
            return new WpdbMirrorBridge(
                $dic->resolve(VersionPressServices::DATABASE),
                $dic->resolve(VersionPressServices::MIRROR),
                $dic->resolve(VersionPressServices::DB_SCHEMA),
                $dic->resolve(VersionPressServices::VPID_REPOSITORY),
                $dic->resolve(VersionPressServices::SHORTCODES_REPLACER)
            );
        });

        $dic->register(VersionPressServices::COMMITTER, function () use ($dic) {
            return new Committer(
                $dic->resolve(VersionPressServices::MIRROR),
                $dic->resolve(VersionPressServices::REPOSITORY),
                $dic->resolve(VersionPressServices::STORAGE_FACTORY)
            );
        });

        $dic->register(VersionPressServices::INITIALIZER, function () use ($dic) {
            return new Initializer(
                $dic->resolve(VersionPressServices::DATABASE),
                $dic->resolve(VersionPressServices::DB_SCHEMA),
                $dic->resolve(VersionPressServices::STORAGE_FACTORY),
                $dic->resolve(VersionPressServices::SYNCHRONIZER_FACTORY),
                $dic->resolve(VersionPressServices::REPOSITORY),
                $dic->resolve(VersionPressServices::URL_REPLACER),
                $dic->resolve(VersionPressServices::VPID_REPOSITORY),
                $dic->resolve(VersionPressServices::SHORTCODES_REPLACER),
                $dic->resolve(VersionPressServices::CHANGEINFO_FACTORY)
            );
        });

        $dic->register(VersionPressServices::SYNCHRONIZER_FACTORY, function () use ($dic) {
            return new SynchronizerFactory(
                $dic->resolve(VersionPressServices::STORAGE_FACTORY),
                $dic->resolve(VersionPressServices::DATABASE),
                $dic->resolve(VersionPressServices::DB_SCHEMA),
                $dic->resolve(VersionPressServices::VPID_REPOSITORY),
                $dic->resolve(VersionPressServices::URL_REPLACER),
                $dic->resolve(VersionPressServices::SHORTCODES_REPLACER)
            );
        });

        $dic->register(VersionPressServices::SYNCHRONIZATION_PROCESS, function () use ($dic) {
            return new SynchronizationProcess($dic->resolve(VersionPressServices::SYNCHRONIZER_FACTORY));
        });

        $dic->register(VersionPressServices::REVERTER, function () use ($dic) {
            return new Reverter(
                $dic->resolve(VersionPressServices::SYNCHRONIZATION_PROCESS),
                $dic->resolve(VersionPressServices::DATABASE),
                $dic->resolve(VersionPressServices::COMMITTER),
                $dic->resolve(VersionPressServices::REPOSITORY),
                $dic->resolve(VersionPressServices::DB_SCHEMA),
                $dic->resolve(VersionPressServices::STORAGE_FACTORY),
                $dic->resolve(VersionPressServices::CHANGEINFO_FACTORY)
            );
        });

        $dic->register(VersionPressServices::REPOSITORY, function () {
            return new GitRepository(VP_PROJECT_ROOT, VERSIONPRESS_TEMP_DIR, VERSIONPRESS_COMMIT_MESSAGE_PREFIX, VP_GIT_BINARY);
        });

        $dic->register(VersionPressServices::VPID_REPOSITORY, function () use ($dic) {
            return new VpidRepository(
                $dic->resolve(VersionPressServices::DATABASE),
                $dic->resolve(VersionPressServices::DB_SCHEMA)
            );
        });

        $dic->register(VersionPressServices::URL_REPLACER, function () {
            return new AbsoluteUrlReplacer(get_home_url());
        });

        $dic->register(VersionPressServices::SHORTCODES_REPLACER, function () use ($dic) {
            return new ShortcodesReplacer(
                $dic->resolve(VersionPressServices::SHORTCODES_INFO),
                $dic->resolve(VersionPressServices::VPID_REPOSITORY)
            );
        });

        $dic->register(VersionPressServices::SHORTCODES_INFO, function () {
            return new ShortcodesInfo(VERSIONPRESS_PLUGIN_DIR . '/src/Database/wordpress-shortcodes.yml');
        });

        $dic->register(VersionPressServices::SQL_QUERY_PARSER, function () use ($dic) {
            return new SqlQueryParser(
                $dic->resolve(VersionPressServices::DB_SCHEMA),
                $dic->resolve(VersionPressServices::DATABASE)
            );
        });

        return self::$instance;
    }
}
