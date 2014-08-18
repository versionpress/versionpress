<?php

class DIContainer {
    /** @var DIContainer */
    private static $instance;
    private $providers;
    private $instances;

    public function register($name, $serviceProvider) {
        $this->providers[$name] = $serviceProvider;
    }

    /**
     * @param $name string Service name
     * @return mixed Service instance
     */
    public function resolve($name) {
        if (!isset($this->instances[$name])) {
            $provider = $this->providers[$name];
            $this->instances[$name] = $provider();
        }
        return $this->instances[$name];
    }

    /**
     * @return DIContainer
     */
    public static function getConfiguredInstance() {
        if(self::$instance != null)
            return self::$instance;

        self::$instance = $dic = new DIContainer();

        $dic->register(VersionPressServices::STORAGE_FACTORY, function () {
            return new EntityStorageFactory(VERSIONPRESS_MIRRORING_DIR);
        });

        $dic->register(VersionPressServices::MIRROR, function () use ($dic) {
            return new Mirror($dic->resolve(VersionPressServices::STORAGE_FACTORY));
        });

        $dic->register(VersionPressServices::DB_SCHEMA, function () {
            global $table_prefix;
            return new DbSchemaInfo(VERSIONPRESS_PLUGIN_DIR . '/src/database/wordpress-schema.neon', $table_prefix);
        });

        $dic->register(VersionPressServices::DATABASE, function () use ($dic) {
            return new MirroringDatabase(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST, $dic->resolve(VersionPressServices::MIRROR), $dic->resolve(VersionPressServices::DB_SCHEMA));
        });

        $dic->register(VersionPressServices::COMMITTER, function () use ($dic) {
            return new Committer($dic->resolve(VersionPressServices::MIRROR));
        });

        $dic->register(VersionPressServices::INITIALIZER, function () use ($dic) {
            return new Initializer($dic->resolve(VersionPressServices::DATABASE), $dic->resolve(VersionPressServices::DB_SCHEMA), $dic->resolve(VersionPressServices::STORAGE_FACTORY));
        });

        $dic->register(VersionPressServices::SYNCHRONIZER_FACTORY, function () use ($dic) {
            return new SynchronizerFactory($dic->resolve(VersionPressServices::STORAGE_FACTORY), $dic->resolve(VersionPressServices::DATABASE), $dic->resolve(VersionPressServices::DB_SCHEMA));
        });

        $dic->register(VersionPressServices::SYNCHRONIZATION_PROCESS, function () use ($dic) {
            return new SynchronizationProcess($dic->resolve(VersionPressServices::SYNCHRONIZER_FACTORY));
        });

        $dic->register(VersionPressServices::REVERTER, function () use ($dic) {
            return new Reverter($dic->resolve(VersionPressServices::SYNCHRONIZATION_PROCESS), $dic->resolve(VersionPressServices::DATABASE), $dic->resolve(VersionPressServices::COMMITTER));
        });

        return self::$instance;
    }
}