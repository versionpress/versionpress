<?php

namespace VersionPress\DI;

use VersionPress\Actions\ActionsInfoProvider;
use VersionPress\Actions\ActionsDefinitionRepository;
use VersionPress\Actions\PluginDefinitionDiscovery;
use VersionPress\ChangeInfos\ChangeInfoFactory;
use VersionPress\ChangeInfos\CommitMessageParser;
use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\ShortcodesInfo;
use VersionPress\Database\ShortcodesReplacer;
use VersionPress\Database\SqlQueryParser;
use VersionPress\Database\TableSchemaStorage;
use VersionPress\Database\VpidRepository;
use VersionPress\Database\WpdbMirrorBridge;
use VersionPress\Git\Committer;
use VersionPress\Git\GitRepository;
use VersionPress\Git\Reverter;
use VersionPress\Initialization\Initializer;
use VersionPress\Storages\Mirror;
use VersionPress\Storages\StorageFactory;
use VersionPress\Synchronizers\SynchronizationProcess;
use VersionPress\Synchronizers\SynchronizerFactory;
use VersionPress\Utils\AbsoluteUrlReplacer;

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
                $dic->resolve(VersionPressServices::CHANGEINFO_FACTORY),
                $dic->resolve(VersionPressServices::TABLE_SCHEMA_STORAGE)
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
                PluginDefinitionDiscovery::getPathsForPlugins('schema.yml'),
                $table_prefix,
                $wp_db_version
            );
        });

        $dic->register(VersionPressServices::ACTIONS_DEFINITION_REPOSITORY, function () use ($dic) {
            return new ActionsDefinitionRepository(VERSIONPRESS_TEMP_DIR . '/actions', $dic->resolve(VersionPressServices::GIT_REPOSITORY));
        });

        $dic->register(VersionPressServices::ACTIONSINFO_PROVIDER_ALL_PLUGINS, function () use ($dic) {
            return new ActionsInfoProvider($dic->resolve(VersionPressServices::ACTIONS_DEFINITION_REPOSITORY)->getAllDefinitionFiles());
        });

        $dic->register(VersionPressServices::ACTIONSINFO_PROVIDER_ACTIVE_PLUGINS, function () {
            return new ActionsInfoProvider(PluginDefinitionDiscovery::getPathsForPlugins('actions.yml'));
        });

        $dic->register(VersionPressServices::CHANGEINFO_FACTORY, function () use ($dic) {
            return new ChangeInfoFactory(
                $dic->resolve(VersionPressServices::DB_SCHEMA),
                $dic->resolve(VersionPressServices::ACTIONSINFO_PROVIDER_ACTIVE_PLUGINS)
            );
        });

        $dic->register(VersionPressServices::COMMIT_MESSAGE_PARSER, function () use ($dic) {
            return new CommitMessageParser(
                $dic->resolve(VersionPressServices::DB_SCHEMA),
                $dic->resolve(VersionPressServices::ACTIONSINFO_PROVIDER_ALL_PLUGINS)
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
                $dic->resolve(VersionPressServices::GIT_REPOSITORY),
                $dic->resolve(VersionPressServices::STORAGE_FACTORY)
            );
        });

        $dic->register(VersionPressServices::INITIALIZER, function () use ($dic) {
            return new Initializer(
                $dic->resolve(VersionPressServices::DATABASE),
                $dic->resolve(VersionPressServices::DB_SCHEMA),
                $dic->resolve(VersionPressServices::STORAGE_FACTORY),
                $dic->resolve(VersionPressServices::SYNCHRONIZER_FACTORY),
                $dic->resolve(VersionPressServices::GIT_REPOSITORY),
                $dic->resolve(VersionPressServices::URL_REPLACER),
                $dic->resolve(VersionPressServices::VPID_REPOSITORY),
                $dic->resolve(VersionPressServices::SHORTCODES_REPLACER),
                $dic->resolve(VersionPressServices::CHANGEINFO_FACTORY),
                $dic->resolve(VersionPressServices::ACTIONS_DEFINITION_REPOSITORY)
            );
        });

        $dic->register(VersionPressServices::SYNCHRONIZER_FACTORY, function () use ($dic) {
            return new SynchronizerFactory(
                $dic->resolve(VersionPressServices::STORAGE_FACTORY),
                $dic->resolve(VersionPressServices::DATABASE),
                $dic->resolve(VersionPressServices::DB_SCHEMA),
                $dic->resolve(VersionPressServices::VPID_REPOSITORY),
                $dic->resolve(VersionPressServices::URL_REPLACER),
                $dic->resolve(VersionPressServices::SHORTCODES_REPLACER),
                $dic->resolve(VersionPressServices::TABLE_SCHEMA_STORAGE)
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
                $dic->resolve(VersionPressServices::GIT_REPOSITORY),
                $dic->resolve(VersionPressServices::DB_SCHEMA),
                $dic->resolve(VersionPressServices::STORAGE_FACTORY),
                $dic->resolve(VersionPressServices::COMMIT_MESSAGE_PARSER)
            );
        });

        $dic->register(VersionPressServices::GIT_REPOSITORY, function () {
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
            return new ShortcodesInfo(PluginDefinitionDiscovery::getPathsForPlugins('shortcodes.yml'));
        });

        $dic->register(VersionPressServices::SQL_QUERY_PARSER, function () use ($dic) {
            return new SqlQueryParser(
                $dic->resolve(VersionPressServices::DB_SCHEMA),
                $dic->resolve(VersionPressServices::DATABASE)
            );
        });

        $dic->register(VersionPressServices::TABLE_SCHEMA_STORAGE, function () use ($dic) {
            return new TableSchemaStorage($dic->resolve(VersionPressServices::DATABASE), VP_VPDB_DIR . '/.schema');
        });

        return self::$instance;
    }
}
