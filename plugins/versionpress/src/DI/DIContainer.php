<?php

namespace VersionPress\DI;

use Committer;
use VersionPress\Configuration\VersionPressConfig;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\WpdbMirrorBridge;
use VersionPress\Database\VpidRepository;
use VersionPress\Utils\AbsoluteUrlReplacer;
use VersionPress\Git\GitRepository;
use VersionPress\Git\Reverter;
use VersionPress\Initialization\Initializer;
use VersionPress\Storages\Mirror;
use VersionPress\Storages\StorageFactory;
use VersionPress\Synchronizers\SynchronizationProcess;
use VersionPress\Synchronizers\SynchronizerFactory;

class DIContainer {
    /** @var DIContainer */
    private static $instance;
    private $providers;
    private $services;

    public function register($name, $serviceProvider) {
        $this->providers[$name] = $serviceProvider;
    }

    /**
     * @param $name string Service name
     * @return mixed Service instance
     */
    public function resolve($name) {
        if (!isset($this->services[$name])) {
            $provider = $this->providers[$name];
            $this->services[$name] = $provider();
        }
        return $this->services[$name];
    }

    /**
     * @return DIContainer
     */
    public static function getConfiguredInstance() {
        if (self::$instance != null)
            return self::$instance;

        self::$instance = $dic = new DIContainer();

        $dic->register(VersionPressServices::VP_CONFIGURATION, function () {
            return new VersionPressConfig();
        });

        $dic->register(VersionPressServices::WPDB, function () {
            global $wpdb;
            return $wpdb;
        });

        $dic->register(VersionPressServices::STORAGE_FACTORY, function () use ($dic) {
            return new StorageFactory(
                VERSIONPRESS_MIRRORING_DIR,
                $dic->resolve(VersionPressServices::DB_SCHEMA),
                $dic->resolve(VersionPressServices::WPDB)
            );
        });

        $dic->register(VersionPressServices::MIRROR, function () use ($dic) {
            return new Mirror(
                $dic->resolve(VersionPressServices::STORAGE_FACTORY),
                $dic->resolve(VersionPressServices::URL_REPLACER)
            );
        });

        $dic->register(VersionPressServices::DB_SCHEMA, function () {
            global $table_prefix;
            return new DbSchemaInfo(VERSIONPRESS_PLUGIN_DIR . '/src/Database/wordpress-schema.neon', $table_prefix);
        });

        $dic->register(VersionPressServices::WPDB_MIRROR_BRIDGE, function () use ($dic) {
            return new WpdbMirrorBridge(
                $dic->resolve(VersionPressServices::WPDB),
                $dic->resolve(VersionPressServices::MIRROR),
                $dic->resolve(VersionPressServices::DB_SCHEMA),
                $dic->resolve(VersionPressServices::VPID_REPOSITORY)
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
                $dic->resolve(VersionPressServices::WPDB),
                $dic->resolve(VersionPressServices::DB_SCHEMA),
                $dic->resolve(VersionPressServices::STORAGE_FACTORY),
                $dic->resolve(VersionPressServices::REPOSITORY),
                $dic->resolve(VersionPressServices::URL_REPLACER),
                $dic->resolve(VersionPressServices::VPID_REPOSITORY)
            );
        });

        $dic->register(VersionPressServices::SYNCHRONIZER_FACTORY, function () use ($dic) {
            return new SynchronizerFactory(
                $dic->resolve(VersionPressServices::STORAGE_FACTORY),
                $dic->resolve(VersionPressServices::WPDB),
                $dic->resolve(VersionPressServices::DB_SCHEMA),
                $dic->resolve(VersionPressServices::URL_REPLACER)
            );
        });

        $dic->register(VersionPressServices::SYNCHRONIZATION_PROCESS, function () use ($dic) {
            return new SynchronizationProcess($dic->resolve(VersionPressServices::SYNCHRONIZER_FACTORY));
        });

        $dic->register(VersionPressServices::REVERTER, function () use ($dic) {
            return new Reverter(
                $dic->resolve(VersionPressServices::SYNCHRONIZATION_PROCESS),
                $dic->resolve(VersionPressServices::WPDB),
                $dic->resolve(VersionPressServices::COMMITTER),
                $dic->resolve(VersionPressServices::REPOSITORY),
                $dic->resolve(VersionPressServices::DB_SCHEMA),
                $dic->resolve(VersionPressServices::STORAGE_FACTORY)
            );
        });

        $dic->register(VersionPressServices::REPOSITORY, function () use ($dic) {
            /** @var VersionPressConfig $vpConfig */
            $vpConfig = $dic->resolve(VersionPressServices::VP_CONFIGURATION);
            return new GitRepository(ABSPATH, VERSIONPRESS_TEMP_DIR, "[VP] ", $vpConfig->gitBinary);
        });

        $dic->register(VersionPressServices::VPID_REPOSITORY, function () use ($dic) {
            return new VpidRepository(
                $dic->resolve(VersionPressServices::WPDB),
                $dic->resolve(VersionPressServices::DB_SCHEMA)
            );
        });

        $dic->register(VersionPressServices::URL_REPLACER, function () {
            return new AbsoluteUrlReplacer(get_site_url());
        });

        return self::$instance;
    }
}